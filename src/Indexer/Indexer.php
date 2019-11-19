<?php

namespace App\Indexer;

use App\Fetcher\Fetcher;
use App\Transformer\DefaultTransformer;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Indexer implements IndexerInterface
{
    const BATCH_SIZE = 100;

    //indexes
    const INDEX_LADDER = 'ladder';
    const INDEX_PLAYERS = 'players';
    const INDEX_SUMMONER_NAMES = 'summoner_names';
    const INDEX_TEAMS = 'teams';
    const INDEX_MEMBERS = 'members';

    //types
    const INDEX_TYPE_LADDER = 'ladder';
    const INDEX_TYPE_PLAYER = 'player';
    const INDEX_TYPE_SUMMONER_NAME = 'summoner_name';
    const INDEX_TYPE_TEAM = 'team';
    const INDEX_TYPE_MEMBER = 'member';

    /**
     * @var string
     */
    private $name;

    /**
     * @var Fetcher
     */
    private $fetcher;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var DefaultTransformer
     */
    private $transformer;

    /**
     * @var NullLogger
     */
    private $logger;

    /**
     * Indexer constructor.
     *
     * @param $name
     */
    public function __construct($name, Index $index, Fetcher $fetcher, DefaultTransformer $transformer, LoggerInterface $logger)
    {
        $this->name = $name;
        $this->fetcher = $fetcher;
        $this->index = $index;
        $this->transformer = $transformer;
        $this->logger = $logger;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addOne(string $typeName, $object): bool
    {
        $this->logger->debug('[Indexer::addOne]', ['name' => $this->name, 'object' => $object]);
        $document = $this->transformer->transform($object, []);

        if (!$document instanceof Document) {
            $this->logger->debug('[Indexer] Could not transform [data]', ['Indexer' => $this->name, 'data' => json_encode($object)]);

            return false;
        }

        $response = $this->index->getType($typeName)->addDocument($document);

        if ($response->isOk()) {
            $this->index->refresh();

            return true;
        }

        return false;
    }

    public function deleteOne(string $typeName, string $uuid): bool
    {
        $this->logger->debug('[Indexer::deleteOne]', ['name' => $this->name, 'uuid' => $uuid]);
        try {
            $document = $this->fetcher->fetchDocument($uuid);

            if (!$document instanceof Document) {
                $this->logger->debug('[Indexer::deleteOne] Document not found', [
                    'Index' => $this->name,
                    'Type' => $typeName,
                    'Document' => $uuid,
                ]);

                return true;
            }

            $response = $this->index->getType($typeName)->deleteDocument($document);

            if ($response->isOk()) {
                $this->index->refresh();

                $this->logger->debug('[Indexer::deleteOne] Document deleted', [
                    'Index' => $this->name,
                    'Type' => $typeName,
                    'Document' => $uuid,
                ]);

                return true;
            }
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    public function updateOne(string $typeName, $updatedObject): bool
    {
        $this->logger->debug('[Indexer::updateOne] Document deleted', [
            'Index' => $this->name,
            'Type' => $typeName,
        ]);

        $document = $this->transformer->transform($updatedObject, []);

        if (!$document instanceof Document) {
            $this->logger->debug('[Indexer] Could not transform {data}', ['Indexer' => $this->name, 'data' => json_encode($updatedObject)]);

            return false;
        }

        $response = $this->index->getType($typeName)->updateDocument($document);

        if ($response->isOk()) {
            $this->index->refresh();

            return true;
        }

        return false;
    }

    public function updateMultiple(string $typeName, array $ids): bool
    {
        if (false === $documents = $this->fetcher->fetchByIds($ids)) {
            return false;
        }

        $updatedDocuments = [];

        foreach ($documents as $document) {
            $updatedDocuments[] = $this->transformer->fetchAndTransform($document, []);
        }

        $response = $this->index->getType($typeName)->updateDocuments($updatedDocuments);

        if ($response->isOk()) {
            $this->index->refresh();

            return true;
        }

        return false;
    }

    public function refresh()
    {
        $this->index->refresh();
    }

    public function addOrUpdateOne(string $typeName, $object): bool
    {
        $this->logger->debug('[Indexer::addOrUpdateOne]', ['name' => $this->name, 'object' => $object]);
        $document = $this->transformer->transform($object, []);

        if (!$document instanceof Document) {
            $this->logger->debug('[Indexer] Could not transform [data]', ['Indexer' => $this->name, 'data' => json_encode($object)]);

            return false;
        }

        $response = (0 === $this->fetcher->fetchByIds($document->getId())) ? $this->index->getType($typeName)->addDocument($document) : $this->index->getType($typeName)->updateDocument($document);

        if ($response->isOk()) {
            $this->index->refresh();

            return true;
        }

        return false;
    }
}
