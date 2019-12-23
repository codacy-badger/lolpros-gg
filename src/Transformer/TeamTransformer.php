<?php

namespace App\Transformer;

use App\Entity\Core\Team\Team;
use App\Indexer\Indexer;
use App\Repository\Core\MemberRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Document;
use Psr\Log\LoggerInterface;

class TeamTransformer extends DefaultTransformer
{
    /**
     * @var MemberRepository
     */
    private $memberRepository;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, MemberRepository $memberRepository)
    {
        parent::__construct($entityManager, $logger);
        $this->memberRepository = $memberRepository;
    }

    public function fetchAndTransform($document, array $fields): ?Document
    {
        $team = $this->entityManager->getRepository(Team::class)->findOneBy(['uuid' => $document['uuid']]);

        if (!$team instanceof Team) {
            return null;
        }

        $document = $this->transform($team, $fields);
        $this->entityManager->clear();

        return $document;
    }

    public function transform($team, array $fields)
    {
        if (!$team instanceof Team) {
            return null;
        }

        $socialMedia = $team->getSocialMedia();
        $region = $team->getRegion();

        $document = [
            'uuid' => $team->getUuidAsString(),
            'name' => $team->getName(),
            'slug' => $team->getSlug(),
            'tag' => $team->getTag(),
            'region' => [
                'uuid' => $region->getUuidAsString(),
                'name' => $region->getName(),
                'slug' => $region->getSlug(),
                'shorthand' => $region->getShorthand(),
                'logo' => $this->buildLogo($region->getLogo()),
            ],
            'logo' => $this->buildLogo($team->getLogo()),
            'active' => (bool) count($this->memberRepository->getCurrentTeamMemberships($team)),
            'creation_date' => $team->getCreationDate()->format(DateTime::ISO8601),
            'disband_date' => $team->getDisbandDate() ? $team->getDisbandDate()->format(DateTime::ISO8601) : null,
            'social_media' => [
                'twitter' => $socialMedia->getTwitter(),
                'website' => $socialMedia->getWebsite(),
                'facebook' => $socialMedia->getFacebook(),
                'leaguepedia' => $socialMedia->getLeaguepedia(),
            ],
            'current_members' => $this->buildMembers($this->memberRepository->getCurrentTeamMemberships($team)),
            'previous_members' => $this->buildMembers($this->memberRepository->getPreviousTeamMemberships($team)),
        ];

        return new Document($team->getUuidAsString(), $document, Indexer::INDEX_TYPE_TEAM, Indexer::INDEX_TEAMS);
    }
}
