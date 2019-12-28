<?php

namespace App\Service;

use Doctrine\Common\Inflector\Inflector;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ErrorFormatter
{
    const FIELD = '/^\[([0-9]+)\]$/';
    const SUB_ERROR = '/^([a-z_]+)$/';
    const SUB_ERROR_FIELD = '/^([a-z_]+)\[([0-9]+)\]$/';

    /**
     * @return Error
     */
    public function reduce(ConstraintViolationListInterface $violationList)
    {
        $error = new Error(true);
        foreach ($violationList as $violation) {
            $errorName = $this->getErrorName($violation);
            if ('' === $violation->getPropertyPath()) {
                $error->addGlobal($errorName);
                continue;
            }
            $path = Inflector::tableize($violation->getPropertyPath());
            if (preg_match(self::FIELD, $path, $matches)) {
                $error->pushField($matches[1], $errorName);
                continue;
            }
            if (preg_match(self::SUB_ERROR, $path, $matches)) {
                $error->getSubError($matches[1])->addGlobal($errorName);
                continue;
            }
            if (preg_match(self::SUB_ERROR_FIELD, $path, $matches)) {
                $error->getSubError($matches[1])->pushField($matches[2], $errorName);
                continue;
            }
            throw new RuntimeException(sprintf('Unmanaged path %s', $violation->getPropertyPath()));
        }

        return $error;
    }

    const NOT_SYNCHRONIZED_ERROR = '1dafa156-89e1-4736-b832-419c2e501fca';
    const NO_SUCH_FIELD_ERROR = '6e5212ed-a197-4339-99aa-5654798a4854';

    public function reduceForm(FormInterface $form)
    {
        $violations = [];

        foreach ($form->getErrors(true) as $error) {
            /** @var ConstraintViolation $violation */
            $violation = $error->getCause();

            if (self::NO_SUCH_FIELD_ERROR === $violation->getCode()) {
                $fields = explode('", "', $violation->getParameters()['{{ extra_fields }}']);
                $error = new Error(true);
                foreach ($fields as $index => $field) {
                    $path = Inflector::tableize($field);

                    $error->pushField($path, 'no_such_field');
                }

                return $error;
            }

            /**
             * Update propertyPath to remove automatically added "data."
             * We need Reflection because we don't have access to $violation::propertyPath.
             */
            $reflectionProperty = new ReflectionProperty(ConstraintViolation::class, 'propertyPath');
            $reflectionProperty->setAccessible(true);
            $path = $violation->getPropertyPath();

            /* Fix data.fooBar pattern */
            $path = str_replace('data.', '', $path);
            /* Fix children[fooBar] pattern */
            $path = preg_replace('/^children\[([a-z]+(?:[A-Z][a-z]+)*)\]$/', '$1', $path);

            $reflectionProperty->setValue($violation, $path);

            $violations[] = $violation;
        }

        return $this->reduce(new ConstraintViolationList($violations));
    }

    /**
     * @return string
     */
    private function getErrorName(ConstraintViolation $violation)
    {
        $errorName = strtolower($violation->getConstraint()->getErrorName($violation->getCode()));
        if (preg_match('/^(.+)_error$/', $errorName, $match)) {
            $errorName = $match[1];
        }

        return $errorName;
    }
}
