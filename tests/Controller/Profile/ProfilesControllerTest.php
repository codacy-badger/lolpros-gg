<?php

namespace App\Tests\Controller\Profile;

use App\Tests\Controller\ControllerTest;

class ProfilesControllerTest extends ControllerTest
{
    const BASE_URL = '/profiles';

    public function testGetProfiles()
    {
        $this->login();
        $this->get(self::BASE_URL);
        $response = $this->getJsonResponse();

        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('total', $response);
        $this->assertObjectHasAttribute('pages', $response);
        $this->assertObjectHasAttribute('current', $response);
        $this->assertObjectHasAttribute('per_page', $response);
        $this->assertObjectHasAttribute('results', $response);
        $this->assertIsArray($response->results);
    }

    public function testGetProfile(string $uuid = '93c73771-7a03-437e-b4e1-cb3dd109aeab')
    {
        $this->login();
        $this->get(self::BASE_URL.'/'.$uuid);
        $response = $this->getJsonResponse();

        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('uuid', $response);
        $this->assertObjectHasAttribute('name', $response);
        $this->assertObjectHasAttribute('slug', $response);
        $this->assertObjectHasAttribute('country', $response);
        $this->assertObjectHasAttribute('memberships', $response);
        $this->assertObjectHasAttribute('social_media', $response);
        $this->assertObjectHasAttribute('regions', $response);
        $this->assertObjectHasAttribute('staff', $response);
        $this->assertObjectHasAttribute('league_player', $response);

        $this->assertEquals($uuid, $response->uuid);
        $this->assertEquals('Don Arts', $response->name);
        $this->assertEquals('don-arts', $response->slug);
        $this->assertEquals('DE', $response->country);
        $this->assertIsArray($response->memberships);
        $this->assertIsArray($response->regions);
        $this->assertIsObject($response->social_media);
    }

    /**
     * @dataProvider postProfileProvider
     */
    public function testPostProfile(array $profile, int $expectedStatus)
    {
        $this->login();
        $this->post(self::BASE_URL, $profile);
        $response = $this->getJsonResponse();

        $this->assertEquals($expectedStatus, $this->client->getResponse()->getStatusCode());

        if (201 !== $expectedStatus) {
            return;
        }

        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('uuid', $response);
        $this->assertObjectHasAttribute('name', $response);
        $this->assertObjectHasAttribute('slug', $response);
        $this->assertObjectHasAttribute('country', $response);
        $this->assertObjectHasAttribute('memberships', $response);
        $this->assertObjectHasAttribute('social_media', $response);
        $this->assertObjectHasAttribute('regions', $response);
        $this->assertObjectHasAttribute('staff', $response);
        $this->assertObjectHasAttribute('league_player', $response);

        $this->assertEquals('Toucouille', $response->name);
        $this->assertEquals('toucouille', $response->slug);
        $this->assertEquals('FR', $response->country);
        $this->assertIsArray($response->memberships);
        $this->assertIsArray($response->regions);
        $this->assertIsObject($response->social_media);
    }

    public function postProfileProvider()
    {
        $baseProfile = [
            'name' => 'Toucouille',
            'country' => 'FR',
            'regions' => [
                '00000000-0000-0000-0000-000000000000',
            ],
            'league_player' => null,
            'staff' => null,
        ];

        return [
            'test valid' => [$baseProfile, 201],
            'test empty' => [[], 409],
            'test no name' => [array_merge($baseProfile, ['name' => null]), 409],
            'test no country' => [array_merge($baseProfile, ['country' => null]), 409],
        ];
    }
}
