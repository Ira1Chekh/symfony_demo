<?php

namespace App\Entity;

use App\Validator\NoLinksOrClickbait;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'symfony_demo_article')]
#[UniqueEntity(fields: ['slug'], errorPath: 'title', message: 'article.slug_unique')]
class Article 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['article', 'article_list'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups(['article', 'article_list'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['article', 'article_list'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank(message: 'article.blank_summary')]
    #[Assert\Length(max: 255)]
    #[NoLinksOrClickbait]
    #[Groups(['article', 'article_list'])]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'article.blank_content')]
    #[Assert\Length(min: 10, minMessage: 'article.too_short_content')]
    #[Groups(['article'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['article', 'article_list'])]
    private \DateTimeImmutable $publishedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['article'])]
    private ?User $author = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'article', orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['publishedAt' => 'DESC'])]
    #[Groups(['article'])]
    private Collection $reviews;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'symfony_demo_article_tag')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Assert\Count(max: 4, maxMessage: 'article.too_many_tags')]
    #[Groups(['article', 'article_list'])]
    private Collection $tags;

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->reviews = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): void
    {
        $review->setArticle($this);

        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
        }
    }

    public function removeReview(Review $review): void
    {
        $this->reviews->removeElement($review);
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    public function addTag(Tag ...$tags): void
    {
        foreach ($tags as $tag) {
            if (!$this->tags->contains($tag)) {
                $this->tags->add($tag);
            }
        }
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }
}