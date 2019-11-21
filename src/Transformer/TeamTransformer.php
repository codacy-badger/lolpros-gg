<?php

namespace App\Transformer;

use App\Entity\Core\Team\Team;
use App\Indexer\Indexer;
use DateTime;
use Elastica\Document;

class TeamTransformer extends DefaultTransformer
{
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
            'active' => (bool) $team->getCurrentMemberships()->count(),
            'creation_date' => $team->getCreationDate()->format(DateTime::ISO8601),
            'disband_date' => $team->getDisbandDate() ? $team->getDisbandDate()->format(DateTime::ISO8601) : null,
            'social_media' => [
                'twitter' => $socialMedia->getTwitter(),
                'website' => $socialMedia->getWebsite(),
                'facebook' => $socialMedia->getFacebook(),
                'leaguepedia' => $socialMedia->getLeaguepedia(),
            ],
            'current_members' => $this->buildMembers($team->getCurrentMemberships()),
            'previous_members' => $this->buildMembers($team->getPreviousMemberships()),
        ];

        return new Document($team->getUuidAsString(), $document, Indexer::INDEX_TYPE_TEAM, Indexer::INDEX_TEAMS);
    }
}
