<?php

namespace App\DataTransformer;

use App\Dto;
use App\Entity;

class ReviewDataTransformer extends AbstractDataTransformer
{
    protected function getResourceClass(): string
    {
        return Entity\Review::class;
    }

    protected function getDtoClass(): string
    {
        return Dto\Review::class;
    }

    /**
     * @param Entity\Review $entity
     */
    protected function output(object $entity): Dto\Review
    {
        $dto = new Dto\Review();
        $dto->setId($entity->getId());
        $dto->setBody($entity->body);

        return $dto;
    }

    /**
     * @param Dto\Review     $dto
     * @param ?Entity\Review $entity
     */
    protected function update(object $dto, ?object $entity): Entity\Review
    {
        $entity ??= new Entity\Review();

        $entity->body = $dto->getBody();

        return $entity;
    }
}
