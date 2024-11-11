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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Attribute\Loggable;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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
   #[Loggable('Creating article')]
   public function create(
    Request $request,
    EntityManagerInterface $entityManager,
    SerializerInterface $serializer,
    ArticleTransformer $articleTransformer,
    ValidatorInterface $validator
): JsonResponse {
       $requestData = $request->getContent();

       // Deserialize to DTO instead of Entity
       /** @var ArticleDTO $articleDTO */
       $articleDTO = $serializer->deserialize($requestData, ArticleDTO::class, 'json', ['groups' => ['article']]);

       // Transform DTO to Entity
       $article = $articleTransformer->transform($articleDTO);

       // Validate the article, including UniqueEntity constraints
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['errors' => $errorsString], 400);
        }

       $entityManager->persist($article);
       $entityManager->flush();

       $data = $serializer->serialize($article, 'json', ['groups' => ['article']]);

       return new JsonResponse(['message' => 'Article created!', 'article' => json_decode($data)], 201);
   }

   #[Route('/{id}', name: 'article_update', methods: ['PUT'])]
   #[IsGranted('edit', 'article', 'Article not found', 404)]
   #[Loggable('Updating article')]
   public function update(Article $article, Request $request,  EntityManagerInterface $entityManager,
   SerializerInterface $serializer,
   ArticleTransformer $articleTransformer,
   #[Autowire(service: 'monolog.logger.database')] LoggerInterface $articleLogger
   ): JsonResponse
   {
        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }

        try {
            $requestData = $request->getContent();

            // Deserialize data to DTO
            /** @var ArticleDTO $articleDTO */
            $articleDTO = $serializer->deserialize($requestData, ArticleDTO::class, 'json', ['groups' => ['article']]);
     
            // Update the article using the transformer
            $updatedArticle = $articleTransformer->transform($articleDTO, $article);
            
            // Persist updates
            $entityManager->flush();

            // Log only after successful save
            $articleLogger->info('Article updated successfully', [
                'title' => $articleDTO->title,
                'slug' => $articleDTO->slug,
                'publishedAt' => $articleDTO->publishedAt->format('Y-m-d H:i:s'),
                'tags' => $articleDTO->tags,
                'summary' => $articleDTO->summary,
            ]);
     
            $data = $serializer->serialize($updatedArticle, 'json', ['groups' => ['article']]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to update article', 'details' => $e->getMessage()], 500);
        }

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