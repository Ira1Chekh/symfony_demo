<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use App\Security\ArticleVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\DTO\ArticleDTO;
use App\Transformer\ArticleTransformer;

#[Route('/api/article')]
#[IsGranted(User::ROLE_EDITOR)]
final class ArticleController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ArticleTransformer $articleTransformer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ArticleTransformer $articleTransformer
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->articleTransformer = $articleTransformer;
    }

    #[Route('/', name: 'article_index', defaults: ['page' => '1'], methods: ['GET'])]
    public function index(Request $request, SerializerInterface $serializer, ArticleRepository $articles, TagRepository $tags): Response
    {
        $tag = null;

        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
        }

        $latestArticles = $articles->findLatest($request->query->get('page', 1), $tag)->getResults();

        $data = $serializer->serialize($latestArticles, 'json', ['groups' => ['article_list']]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{slug:article}', name: 'article_show', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function show(Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::SHOW, $article, 'Articles can only be shown to their authors.');

       $result = $this->serializer->serialize($article, 'json', ['groups' => ['article']]);

        return new Response($result, 200, ['Content-Type' => 'application/json']);
    }

   #[Route('/', name: 'article_create', methods: ['POST'])]
   public function create(
    Request $request,
    EntityManagerInterface $entityManager,
    SerializerInterface $serializer,
    ArticleTransformer $articleTransformer
): JsonResponse {
       $requestData = $request->getContent();

       // Deserialize to DTO instead of Entity
       /** @var ArticleDTO $articleDTO */
       $articleDTO = $serializer->deserialize($requestData, ArticleDTO::class, 'json', ['groups' => ['article']]);

       // Transform DTO to Entity
       $article = $articleTransformer->transform($articleDTO);

       $entityManager->persist($article);
       $entityManager->flush();

       $data = $serializer->serialize($article, 'json', ['groups' => ['article']]);

       return new JsonResponse(['message' => 'Article created!', 'article' => json_decode($data)], 201);
   }

   #[Route('/{id}', name: 'article_update', methods: ['PUT'])]
   public function update(int $id, Request $request,  EntityManagerInterface $entityManager,
   SerializerInterface $serializer,
   ArticleTransformer $articleTransformer): JsonResponse
   {
       // Fetch existing article
       $article = $entityManager->getRepository(Article::class)->find($id);

       if (!$article) {
           return new JsonResponse(['error' => 'Article not found'], 404);
       }

       $requestData = $request->getContent();

       // Deserialize data to DTO
       /** @var ArticleDTO $articleDTO */
       $articleDTO = $serializer->deserialize($requestData, ArticleDTO::class, 'json', ['groups' => ['article']]);

       // Update the article using the transformer
       $updatedArticle = $articleTransformer->transform($articleDTO, $article);
       
       // Persist updates
       $entityManager->flush();

       $data = $serializer->serialize($updatedArticle, 'json', ['groups' => ['article']]);

       return new JsonResponse(['message' => 'Article updated!', 'article' => json_decode($data)], 200);
   }

   #[Route('/{id}', name: 'article_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Fetch existing article
        $article = $this->entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }

        $article->getTags()->clear();
        // Remove the article
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Article deleted'], 200);
    }

}