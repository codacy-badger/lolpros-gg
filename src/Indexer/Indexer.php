<?php

namespace App\Indexer;

use App\Fetcher\Fetcher;
use App\Transformer\DefaultTransformer;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Response;
use Exception;
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

        return $this->handleResponse($response);
    }

    public function deleteOne(string $typeName, string $uuid): bool
    {
        try {
            $this->logger->debug('[Indexer::deleteOne]', ['name' => $this->name, 'uuid' => $uuid]);
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

            return $this->handleResponse($response);
        } catch (NotFoundException $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }

    public function deleteMultiple(string $typeName, array $ids): bool
    {
        try {
            $this->logger->debug('[Indexer::deleteMultiple]', ['name' => $this->name, 'total' => count($ids)]);
            $documents = $this->fetcher->fetchByIds($ids);

            if (!count($documents)) {
                $this->logger->error(sprintf('[Indexer::deleteMultiple] Couldnt find documents in index %s', $typeName), $ids);

                return false;
            }

            $response = $this->index->getType($typeName)->deleteDocuments($documents);

            return $this->handleResponse($response);
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[Indexer::deleteMultiple] %s', $e->getMessage()));
        }

        return false;
    }

    public function updateOne(string $typeName, $updatedObject): bool
    {
        try {
            $this->logger->debug('[Indexer::updateOne]', ['name' => $this->name, 'uuid' => $updatedObject]);
            $document = $this->transformer->transform($updatedObject, []);

            if (!$document instanceof Document) {
                $this->logger->debug('[Indexer] Could not transform {data}', ['Indexer' => $this->name, 'data' => json_encode($updatedObject)]);

                return false;
            }

            $response = $this->index->getType($typeName)->updateDocument($document);

            return $this->handleResponse($response);
        } catch (NotFoundException $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }

    public function updateMultiple(string $typeName, array $ids): bool
    {
        try {
            $this->logger->debug('[Indexer::updateMultiple]', ['name' => $this->name, 'total' => count($ids)]);
            $documents = $this->fetcher->fetchByIds($ids);

            if (!count($documents)) {
                $this->logger->error(sprintf('[Indexer::updateMultiple] Couldnt find documents in index %s', $typeName), $ids);

                return false;
            }

            $updatedDocuments = [];

            foreach ($documents as $document) {
                $updatedDocuments[] = $this->transformer->fetchAndTransform($document, []);
            }

            $response = $this->index->getType($typeName)->updateDocuments($updatedDocuments);

            return $this->handleResponse($response);
        } catch (NotFoundException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[Indexer::updateMultiple] %s', $e->getMessage()));
        }

        return false;
    }

    public function addOrUpdateOne(string $typeName, $object): bool
    {
        try {
            $this->logger->debug('[Indexer::addOrUpdateOne]', ['name' => $this->name, 'object' => $object]);
            $document = $this->transformer->transform($object, []);

            if (!$document instanceof Document) {
                $this->logger->debug('[Indexer] Could not transform [data]', ['Indexer' => $this->name, 'data' => json_encode($object)]);

                return false;
            }

            $documents = $this->fetcher->fetchByIds($document->getId());
            $response = !count($documents) ? $this->index->getType($typeName)->addDocument($document) : $this->index->getType($typeName)->updateDocument($document);

            return $this->handleResponse($response);
        } catch (NotFoundException $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }

    private function handleResponse(Response $response): bool
    {
        if (!$response->isOk()) {
            return false;
        }

        $this->index->refresh();

        return true;
    }
}
