<?php

namespace App\Controller;

use App\Service\ErrorFormatter;
use Doctrine\Common\Inflector\Inflector;
use Exception;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class APIController extends AbstractFOSRestController
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ErrorFormatter
     */
    protected $errorFormatter;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(SerializerInterface $serializer, ErrorFormatter $errorFormatter, RequestStack $requestStack)
    {
        $this->serializer = $serializer;
        $this->errorFormatter = $errorFormatter;
        $this->requestStack = $requestStack;
    }

    protected function serialize($data, $groups = null, int $code = 200): Response
    {
        $context = (new Context())->setGroups($groups ? (array) $groups : null)->setSerializeNull(true);

        return $this->handleView($this->view($data, $code)->setContext($context)->setFormat('json'));
    }

    protected function deserialize(string $class, string $group = null)
    {
        if (!($content = $this->requestStack->getCurrentRequest()->getContent())) {
            return $content;
        }

        try {
            $context = DeserializationContext::create();
            $context->setGroups([$group]);

            return $this->serializer->deserialize($content, $class, 'json', $context);
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    protected function getPostedData($data = null): array
    {
        $rawData = (array) $data;

        if (!$data) {
            $content = $this->requestStack->getCurrentRequest()->getContent();

            $rawData = json_decode($content, true);

            if (!is_array($rawData)) {
                throw new BadRequestHttpException();
            }
        }

        $correctData = [];

        unset($rawData['uuid']);
        foreach ($rawData as $field => $value) {
            $correctData[Inflector::camelize($field)] = $value;
        }

        return $correctData;
    }

    protected function find(string $namespace, string $uuid)
    {
        $entity = $this->getDoctrine()->getRepository($namespace)->findOneBy(['uuid' => $uuid]);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    protected function findOne(array $namespaces, string $uuid)
    {
        foreach ($namespaces as $namespace) {
            $entity = $this->getDoctrine()->getRepository($namespace)->findOneBy([
                'uuid' => $uuid,
            ]);
            if ($entity) {
                break;
            }
        }

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    protected function findBy(string $namespace, array $params)
    {
        $entity = $this->getDoctrine()->getRepository($namespace)->findBy($params);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    protected function getNullOrBoolean(?string $param): ?bool
    {
        if (null !== $param) {
            return filter_var($param, FILTER_VALIDATE_BOOLEAN);
        }

        return null;
    }
}
