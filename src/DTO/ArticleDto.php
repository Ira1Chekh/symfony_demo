<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\User;

class ArticleDto
{
    #[Groups(['article'])]
    public ?int $id = null;

    #[Groups(['article'])]
    public string $title;

    #[Groups(['article'])]
    public string $slug;

    #[Groups(['article'])]
    public string $summary;

    #[Groups(['article'])]
    public \DateTimeInterface $publishedAt;

    #[Groups(['article'])]
    public array $tags;

    #[Groups(['article'])]
    public string $content;

    #[Groups(['article'])]
    public User $author;
}