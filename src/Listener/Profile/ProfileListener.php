<?php

namespace App\Listener\Profile;

use App\Entity\Profile\Profile;
use App\Event\Profile\ProfileEvent;
use App\Indexer\Indexer;
use App\Manager\Report\AdminLogManager;
use App\Repository\MemberRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProfileListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AdminLogManager
     */
    protected $adminLogManager;

    /**
     * @var Indexer
     */
    private $playerIndexer;

    /**
     * @var Indexer
     */
    private $membersIndexer;
    /**
     * @var MemberRepository
     */
    private $membersRepository;

    public static function getSubscribedEvents()
    {
        return [
            ProfileEvent::CREATED => 'onCreate',
            ProfileEvent::UPDATED => 'onUpdate',
            ProfileEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $playerIndexer, Indexer $membersIndexer, MemberRepository $membersRepository)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->playerIndexer = $playerIndexer;
        $this->membersIndexer = $membersIndexer;
        $this->membersRepository = $membersRepository;
    }

    public function onCreate(ProfileEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Profile) {
            return;
        }

        $this->playerIndexer->addOne(Indexer::INDEX_TYPE_PROFILE, $entity);
        $this->adminLogManager->createLog(ProfileEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(ProfileEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Profile) {
            return;
        }

        $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PROFILE, $entity);
        $this->membersIndexer->updateMultiple(Indexer::INDEX_TYPE_MEMBER, $this->membersRepository->getMembersUuidsFromPlayer($entity));
        $this->adminLogManager->createLog(ProfileEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(ProfileEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Profile) {
            return;
        }

        $this->playerIndexer->deleteOne(Indexer::INDEX_TYPE_PROFILE, $entity->getUuidAsString());
        $this->membersIndexer->deleteMultiple(Indexer::INDEX_TYPE_MEMBER, $this->membersRepository->getMembersUuidsFromPlayer($entity));
        $this->adminLogManager->createLog(ProfileEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}