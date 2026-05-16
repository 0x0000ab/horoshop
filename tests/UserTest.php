<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{

    private $client;

    public function testSomething(): void
    {
        $this->client = static::createClient();

        $defaultRootAccessToken = 'root_token';

        // Not found user
        $this->client->request(
            'GET',
            '/v1/api/users/1000',
            server: $this->defHeader($defaultRootAccessToken)
        );

        $data = $this->resp($this->client);

        $errorCode = $data['error']['code'] ?? null;
        $this->assertTrue($errorCode == 404, '404');

        // Auth fail
        $this->client->request(
            'GET',
            '/v1/api/users/1000'
        );

        $data = $this->resp($this->client);

        $errorCode = $data['error']['code'] ?? null;
        $this->assertTrue($errorCode == 401, '401');


        // Create user
        $testUser = 'a@u.com';
        list($userId, $p) = $this->createUser($defaultRootAccessToken, $testUser);

        if (!$userId) {
            throw new \Exception('Failed to create user ' . $testUser);
        }

        // Update user
        $testUser = 'a1@u.com';
        $this->updateUser($userId, $defaultRootAccessToken, $testUser);

        // Get user
        $this->client->request(
            'GET',
            '/v1/api/users/' . $userId,
            server: $this->defHeader($defaultRootAccessToken)
        );

        $data = $this->resp($this->client);

        $userId = $data['data']['id'] ?? null;
        $login = $data['data']['login'] ?? null;
        $pass = $data['data']['pass'] ?? null;
        $accessToken = $data['data']['accessToken'] ?? null;

        $this->assertTrue(isset($data['status']) && $data['status'] == 1, 'Get user ' . $testUser);
        $this->assertTrue($login == $testUser, 'login - ' . $testUser);
        $this->assertTrue($pass == $testUser, 'login - ' . $testUser);

        $testUser = 'b@b.com';
        list($subUserId, $accessToken) = $this->createUser($accessToken, $testUser);

        $testUser = 'b1@b.com';
        $this->updateUser($subUserId, $accessToken, $testUser);

        $this->delete($subUserId, $defaultRootAccessToken);

        $this->delete($userId, $defaultRootAccessToken);
    }


    private function createUser($token, $testUser)
    {
        $this->client->request(
            'POST',
            '/v1/api/users',
            server: $this->defHeader($token),
            content: json_encode([
                'login' => $testUser,
                'phone' => '922',
                'pass' => $testUser
            ])
        );

        $data = $this->resp($this->client);
        $userId = $data['data']['id'] ?? null;
        $login = $data['data']['login'] ?? null;
        $pass = $data['data']['pass'] ?? null;
        $accessToken = $data['data']['accessToken'] ?? null;

        $this->assertTrue(isset($data['status']) && $data['status'] == 1, 'Create user ' . $testUser);
        $this->assertTrue($login == $testUser, 'login - ' . $testUser);
        $this->assertTrue($pass == $testUser, 'login - ' . $testUser);

        return [$userId, $accessToken];
    }

    private function updateUser($userId, $token, $testUser)
    {

        $this->client->request(
            'PUT',
            '/v1/api/users/' . $userId,
            server: $this->defHeader($token),
            content: json_encode([
                'login' => $testUser,
                'phone' => '923',
                'pass' => $testUser
            ])
        );

        $data = $this->resp($this->client);

        $userId = $data['data']['id'] ?? null;
        $login = $data['data']['login'] ?? null;
        $pass = $data['data']['pass'] ?? null;

        $this->assertTrue(isset($data['status']) && $data['status'] == 1, 'Update user ' . $testUser);
        $this->assertTrue($login == $testUser, 'login - ' . $testUser);
        $this->assertTrue($pass == $testUser, 'login - ' . $testUser);
    }

    private function delete($userId, $token)
    {
        // Delete user
        $this->client->request(
            'DELETE',
            '/v1/api/users/' . $userId,
            server: $this->defHeader($token)
        );

        $data = $this->resp($this->client);

        $this->assertTrue(isset($data['status']) && $data['status'] == 1, 'Deleted user ' . $testUser);
    }


    private function resp($client)
    {
        $response = $client->getResponse();
        return json_decode($response->getContent(), true);
    }

    private function defHeader($token)
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];
    }

}
