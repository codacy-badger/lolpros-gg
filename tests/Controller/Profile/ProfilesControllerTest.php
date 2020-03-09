<?php

namespace App\Tests\Controller\Profile;

use App\Tests\Controller\ControllerTest;

class ProfilesControllerTest extends ControllerTest
{
    public function testGetProfiles()
    {
        $this->login();
        $this->get('/profiles');
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
        $this->get('/profiles/'.$uuid);
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
}
