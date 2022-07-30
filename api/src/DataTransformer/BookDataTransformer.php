<?php

namespace App\DataTransformer;

use App\Dto;
use App\Entity;

class BookDataTransformer extends AbstractDataTransformer
{
    protected function getResourceClass(): string
    {
        return Entity\Book::class;
    }

    protected function getDtoClass(): string
    {
        return Dto\Book::class;
    }

    /**
     * @param Entity\Book $entity
     */
    protected function output(object $entity): Dto\Book
    {
        $dto = new Dto\Book();
        $dto->setId($entity->getUuid());
        $dto->setTitle($entity->title);
        $dto->setDescription($entity->description);
        $dto->setAuthor($entity->author);
        $dto->setIsbn($entity->isbn);
        $dto->setPublicationDate($entity->publicationDate?->format('Y-m-d'));
        $dto->setReviews(iterator_to_array($entity->getReviews()));

        return $dto;
    }

    /**
     * @param Dto\Book     $dto
     * @param ?Entity\Book $entity
     */
    protected function update(object $dto, ?object $entity): Entity\Book
    {
        $entity ??= new Entity\Book();

        $entity->isbn = $dto->getIsbn();
        $entity->title = $dto->getTitle();
        $entity->author = $dto->getAuthor();
        $entity->description = $dto->getDescription();
        $entity->publicationDate = \DateTime::createFromFormat('Y-m-d', $dto->getPublicationDate());

        return $entity;
    }
}
