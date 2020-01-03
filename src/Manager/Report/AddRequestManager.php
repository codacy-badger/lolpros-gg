<?php

namespace App\Manager\Report;

use App\Entity\Report\AddRequest;
use App\Exception\Core\EntityNotCreatedException;
use App\Exception\Core\EntityNotDeletedException;
use App\Exception\Core\EntityNotUpdatedException;
use App\Manager\DefaultManager;
use Exception;

class AddRequestManager extends DefaultManager
{
    public function create(AddRequest $addRequest): AddRequest
    {
        try {
            $this->entityManager->persist($addRequest);
            $this->entityManager->flush();

            return $addRequest;
        } catch (Exception $e) {
            $this->logger->error('[AddRequestsManager] Could not create addRequest because of {reason}', [
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotCreatedException(AddRequest::class, $e->getMessage());
        }
    }

    public function update(AddRequest $addRequest): AddRequest
    {
        try {
            $this->entityManager->flush();

            return $addRequest;
        } catch (Exception $e) {
            $this->logger->error('[AddRequestsManager] Could not update addRequest {uuid} because of {reason}', [
                'uuid' => $addRequest->getUuid()->toString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(AddRequest::class, $addRequest->getUuid()->toString(), $e->getMessage());
        }
    }

    public function delete(AddRequest $addRequest)
    {
        try {
            $this->entityManager->remove($addRequest);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error('[AddRequestsManager] Could not delete addRequest {uuid} because of {reason}', [
                'uuid' => $addRequest->getUuid()->toString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(AddRequest::class, $addRequest->getUuid()->toString(), $e->getMessage());
        }
    }
}
