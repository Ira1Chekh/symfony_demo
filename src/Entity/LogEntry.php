<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LogEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $channel;

    #[ORM\Column(type: 'string')]
    private string $level;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'json')]
    private array $context;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
