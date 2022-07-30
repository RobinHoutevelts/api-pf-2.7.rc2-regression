<?php

namespace App\Dto;

use App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @see Entity\Review
 * @see \App\DataTransformer\ReviewDataTransformer
 */
class Review
{
    public string $id;
    public string $body;

    #[Groups(groups: ['book:read'])]
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    #[Groups(groups: ['book:read'])]
    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}
