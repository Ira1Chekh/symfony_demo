<?php

namespace App\Transformer;

use App\DTO\ArticleDTO;
use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ArticleTransformer
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function transform(ArticleDTO $articleDTO, Article $article = null): Article
    {
        if ($article === null) {
            $article = new Article();
        }
       
        $article->setTitle($articleDTO->title);
        $article->setSlug($articleDTO->slug);
        $article->setSummary($articleDTO->summary);
        $article->setPublishedAt($articleDTO->publishedAt);
        $article->setContent($articleDTO->content);
        $user = $this->security->getUser();

        if ($user instanceof User) {  // Ensure user is of type User
            $article->setAuthor($user);
        }

        foreach ($articleDTO->tags as $tagData) {
            $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['name' => $tagData['name']]);
            if (!$tag) {
                $tag = new Tag($tagData['name']);  // Assuming Tag constructor requires a name.
                $this->entityManager->persist($tag);
            }
            $article->addTag($tag);
        }

        return $article;
    }
}
