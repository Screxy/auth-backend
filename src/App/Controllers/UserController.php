<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ArrayValidator;
use App\Models\User;
use Core\Logger;
use Core\NotFoundResponse;
use Core\Response;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

class UserController
{
    public static function authorize(array $requestData): void
    {
        try {
            ArrayValidator::validateKeysOnEmpty(['email', 'password'], $requestData);

            $user = User::getByEmail($requestData['email']);
            if ($user === null) {
                echo NotFoundResponse::create();

                return;
            }

            $isValid = password_verify($requestData['password'], $user->getPassword());

            if (!$isValid) {
                echo new Response(401, ['message' => 'unauthorized']);

                return;
            }
            $key = (string)getenv('APP_KEY');

            $payload = [
                'user_id' => $user->getId(),
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            echo new Response(200, ['access_token' => $jwt]);

        } catch (InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());
            echo new Response(400, ['message' => $exception->getMessage()]);
        }
    }

    public static function register(array $requestData): void
    {
        try {
            ArrayValidator::validateKeysOnEmpty(['email', 'password'], $requestData);


            if (User::getByEmail($requestData['email'])) {
                echo new Response(409, ['message' => 'User already exist']);

                return;
            }

            $user = new User();
            $user->setEmail($requestData['email']);
            $user->setPassword($requestData['password']);
            $user->save();
        } catch (InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());
            echo new Response(400);
        }

    }

    public static function feed(array $requestData): void
    {
        try {
            ArrayValidator::validateKeysOnEmpty(['access_token'], $requestData);
            $key = (string)getenv('APP_KEY');
            JWT::decode($requestData['access_token'], new Key($key, 'HS256'));
            echo new Response(200);
        } catch (InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());
            echo new Response(400, ['message' => $exception->getMessage()]);
        } catch (DomainException $exception) {
            Logger::error($exception->getTrace());
            echo new Response(401, ['message' => 'unauthorized']);
        }
    }
}
