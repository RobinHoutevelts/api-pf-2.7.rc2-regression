<?php

namespace App\Dto;

use App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see Entity\Book
 * @see \App\DataTransformer\BookDataTransformer
 */
class Book
{
    private string $id;
    private string $isbn;
    private string $title;
    private string $description;
    private string $author;
    private string $publicationDate;
    private array $reviews = [];

    #[Groups(groups: ['book:read'])]
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'book:write'])]
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }

    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'book:write'])]
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'book:write'])]
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'book:write'])]
    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    #[Assert\NotBlank]
    #[Assert\Date]
    #[Groups(groups: ['book:read', 'book:write'])]
    public function getPublicationDate(): string
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(string $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    /** @return Entity\Review[] */
    #[Groups(groups: ['book:read', 'book:write'])]
    public function getReviews(): array
    {
        return $this->reviews;
    }

    public function setReviews(array $reviews): void
    {
        $this->reviews = $reviews;
    }
}
