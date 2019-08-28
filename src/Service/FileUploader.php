<?php

namespace App\Service;

use App\Entity\Core\Document\Document;
use App\Entity\Core\Document\TeamLogo;
use App\Entity\Core\Team\Team;
use App\Entity\LeagueOfLegends\Document\RegionLogo;
use App\Entity\LeagueOfLegends\Region\Region;
use App\Event\Core\Team\TeamEvent;
use App\Event\LeagueOfLegends\Region\RegionEvent;
use App\Manager\DefaultManager;
use Cloudinary\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader extends DefaultManager
{
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, $cloudinaryName, $cloudinaryApiKey, $cloudinaryApiSecret)
    {
        parent::__construct($entityManager, $logger, $eventDispatcher);

        \Cloudinary::config([
            'cloud_name' => $cloudinaryName,
            'api_key' => $cloudinaryApiKey,
            'api_secret' => $cloudinaryApiSecret,
        ]);
    }

    public function uploadTeamLogo(UploadedFile $file, Team $team): Document
    {
        $this->logger->debug('[FileUploader::uploadTeamLogo]');

        $upload = Uploader::upload($file, [
            'resource_type' => 'image',
            'public_id' => 'teams/'.$team->getSlug(),
        ]);

        if ($logo = $team->getLogo()) {
            $logo->setPublicId($upload['public_id']);
            $logo->setUrl($upload['url']);
            $logo->setVersion($upload['version']);
            $logo->setTeam($team);
        } else {
            $logo = new TeamLogo();
            $logo->setPublicId($upload['public_id']);
            $logo->setUrl($upload['url']);
            $logo->setVersion($upload['version']);
            $logo->setTeam($team);

            $team->setLogo($logo);
            $this->entityManager->persist($logo);
        }

        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new TeamEvent($team), TeamEvent::UPDATED);

        return $logo;
    }

    public function uploadRegionLogo(UploadedFile $file, Region $region): Document
    {
        $this->logger->debug('[FileUploader::uploadRegionLogo]');

        $upload = Uploader::upload($file, [
            'resource_type' => 'image',
            'public_id' => 'regions/'.$region->getSlug(),
        ]);

        if ($logo = $region->getLogo()) {
            $logo->setPublicId($upload['public_id']);
            $logo->setUrl($upload['secure_url']);
            $logo->setVersion($upload['version']);
            $logo->setRegion($region);
        } else {
            $logo = new RegionLogo();
            $logo->setPublicId($upload['public_id']);
            $logo->setUrl($upload['secure_url']);
            $logo->setVersion($upload['version']);
            $logo->setRegion($region);

            $region->setLogo($logo);
            $this->entityManager->persist($logo);
        }

        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new RegionEvent($region), RegionEvent::UPDATED);

        return $logo;
    }
}
