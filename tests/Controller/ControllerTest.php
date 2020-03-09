<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class ControllerTest extends WebTestCase
{
    const DEFAULT_USERNAME = 'chypriote';
    const DEFAULT_PASSWORD = 'kroxigor555';

    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Create a client with a default Authorization header.
     */
    protected function login($username = self::DEFAULT_USERNAME, $password = self::DEFAULT_PASSWORD): void
    {
        $this->client->request('POST', '/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => $username,
            'password' => $password,
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
    }

    protected function get($uri, array $parameters = []): Response
    {
        $this->client->request('GET', $uri, $parameters);

        return $this->client->getResponse();
    }

    protected function post($uri, array $data = []): Response
    {
        $this->client->request('POST', $uri, [], [], [], json_encode($data));

        return $this->client->getResponse();
    }

    protected function put($uri, array $data = []): Response
    {
        $this->client->request('PUT', $uri, [], [], [], json_encode($data));

        return $this->client->getResponse();
    }

    protected function delete($uri, array $parameters = []): Response
    {
        $this->client->request('DELETE', $uri, $parameters);

        return $this->client->getResponse();
    }

    protected function getJsonResponse()
    {
        return json_decode($this->client->getResponse()->getContent());
    }
}
