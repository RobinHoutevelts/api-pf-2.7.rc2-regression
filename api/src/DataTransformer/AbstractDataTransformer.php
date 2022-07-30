<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;

abstract class AbstractDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function transform($object, string $to, array $context = []): object
    {
        if (empty($context['api_denormalize'])) {
            return $this->output($object);
        }

        $this->validator->validate($object);

        return $this->update($object, $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        $resourceClass = $this->getResourceClass();
        $dtoClass = $this->getDtoClass();

        if (!empty($context['api_denormalize'])) {
            if ($data instanceof $resourceClass) {
                // already transformed
                return false;
            }

            return $to === $resourceClass
                && ($context['input']['class'] ?? null) === $dtoClass;
        }

        return $data instanceof $resourceClass
            && $to === $dtoClass;
    }

    abstract protected function getResourceClass(): string;

    abstract protected function getDtoClass(): string;

    abstract protected function output(object $entity): object;

    abstract protected function update(object $dto, ?object $entity): object;
}
