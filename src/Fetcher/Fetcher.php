<?php

namespace App\Fetcher;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Ids;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Fetcher
{
    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * @var Index
     */
    protected $type;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Fetcher constructor.
     */
    public function __construct(Index $type, LoggerInterface $logger)
    {
        $this->type = $type;
        $this->logger = $logger;
        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
    }

    public function fetch(array $options): array
    {
        $options = $this->resolver->resolve($options);
        $results = [];

        while ($resultsPerPage = $this->fetchByPage($options)) {
            ++$options['page'];
            $results = array_merge($results, $resultsPerPage);
        }

        return $results;
    }

    public function fetchByPage(array $options): array
    {
        $options = $this->resolver->resolve($options);

        $query = $this->createQuery($options);

        return array_map(
            function (Document $document) {
                return $document->getData();
            },
            $this->type->search($query)->getDocuments()
        );
    }

    public function fetchOne(array $options): array
    {
        $options = $this->resolver->resolve($options);

        $results = $this->type->search($this->createQuery($options))->getDocuments();

        if (count($results)) {
            return $results[0]->getData();
        }

        return [];
    }

    public function fetchDocument($uuid): ?Document
    {
        try {
            $query = new Ids();
            $query->setIds($uuid);

            $documents = $this->type->search($query)->getDocuments();

            if (count($documents)) {
                return $documents[0];
            }

            return null;
        } catch (ResponseException $e) {
            $this->logger->error('[fetcher] try to fetch document {uuid}', ['uuid' => $uuid]);

            return null;
        }
    }

    public function fetchByIds($ids): array
    {
        try {
            $ids = (array) $ids;

            $query = new Ids();
            $query->setIds($ids);

            $documents = $this->type->search($query, ['limit' => count($ids)])->getDocuments();

            if (!count($documents)) {
                return [];
            }

            return array_map(function (Document $doc) { return $doc->getData(); }, $documents);
        } catch (ResponseException $e) {
            $this->logger->error('[fetcher] try to fetch documents {ids}', ['ids' => $ids]);

            return [];
        }
    }

    abstract protected function createQuery(array $options): Query;

    abstract protected function configureOptions(OptionsResolver $resolver): OptionsResolver;
}
