<?php

namespace App\Manager\Core\Medal;

use App\Entity\Core\Medal\AMedal;
use App\Entity\Core\Medal\PlayerMedal;
use App\Entity\LeagueOfLegends\Medal\PlayerMedal as LoLPlayerMedal;
use App\Entity\LeagueOfLegends\Medal\RiotAccountMedal;
use App\Exception\Core\EntityNotCreatedException;
use App\Manager\DefaultManager;
use Exception;

class MedalManager extends DefaultManager
{
    public function create(AMedal $medalData): AMedal
    {
        switch (true) {
            case $medalData instanceof PlayerMedal:
                $medal = new PlayerMedal(); break;
            case $medalData instanceof LoLPlayerMedal:
                $medal = new LoLPlayerMedal(); break;
            case $medalData instanceof RiotAccountMedal:
                $medal = new RiotAccountMedal(); break;
            default:
                throw new EntityNotCreatedException('unrecognized medal type');
        }

        $medal->setName($medalData->getName());

        $this->entityManager->persist($medal);
        $this->entityManager->flush();

        return $medal;
    }
}
