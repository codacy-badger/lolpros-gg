<?php

namespace App\Command;

use App\Entity\Core\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use RiotAPI\LeagueAPI\Exceptions\RequestException;
use RiotAPI\LeagueAPI\Exceptions\ServerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateUsersUuid extends Command
{
    protected static $defaultName = 'fol:user:uuid';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates an uuid for all users that don\'t have one')
            ->addOption('force', 'f');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $this->logger->info('[GenerateUsersUuid] Starting');
        foreach ($this->entityManager->getRepository(User::class)->findAll() as $user) {
            if ($user->getUuid() && false === $input->getOption('force')) {
                continue;
            }

            try {
                $user->setUuid(Uuid::uuid4());
                $this->entityManager->flush($user);
                $this->logger->info(sprintf('[GenerateUsersUuid] Generated UUID for user %s (%s)', $user->getUuid()->toString(), $user->getUsername()));
            } catch (RequestException $e) {
                $this->logger->critical(sprintf('[GenerateUsersUuid] Did not generate uuid %s because: %s', $user->getUsername(), $e->getMessage()));
            } catch (ServerException $e) {
                $this->logger->critical(sprintf('Server exception %s', $e->getMessage()));
            }
        }
        $this->logger->info(sprintf('[GenerateUsersUuid] Generated all Uuids in %s seconds.', microtime(true) - $start));
    }
}
