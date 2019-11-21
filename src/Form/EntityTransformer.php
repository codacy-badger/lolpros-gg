<?php

namespace App\Form;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $className;

    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
        $this->className = $this->repository->getClassName();
    }

    public function transform($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return bool|object|object[]|null
     */
    public function reverseTransform($value)
    {
        if (!is_array($value) || !isset($value['uuid'])) {
            return null;
        }

        $entity = $this->repository->findOneBy(['uuid' => $value['uuid']]);

        if (!$entity) {
            throw new TransformationFailedException(sprintf('Impossible to find issue %s', $uuid));
        }

        return $entity;
    }
}
