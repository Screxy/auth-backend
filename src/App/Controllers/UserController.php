<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enum\PasswordStrength;
use App\Helpers\ArrayValidator;
use App\Models\User;
use Core\Logger;
use Core\NotFoundResponse;
use Core\Response;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use ZxcvbnPhp\Zxcvbn;

class UserController
{
    public static function authorize(array $requestData): Response
    {
        try {
            ArrayValidator::validateKeysOnEmpty(['email', 'password'], $requestData);

            $user = User::getByEmail($requestData['email']);
            if ($user === null) {
                return NotFoundResponse::create();
            }

            $isValid = password_verify($requestData['password'], $user->getPassword());

            if (!$isValid) {
                return new Response(401, ['message' => 'unauthorized']);
            }
            $key = (string)getenv('APP_KEY');

            $payload = [
                'user_id' => $user->getId(),
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            return new Response(200, ['access_token' => $jwt]);

        } catch (InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());
            return new Response(400, ['message' => $exception->getMessage()]);
        }
    }

    public static function register(array $requestData): Response
    {
        try {
            ArrayValidator::validateKeysOnEmpty(['email', 'password'], $requestData);


            if (User::getByEmail($requestData['email'])) {
                return new Response(409, ['message' => 'User already exist']);
            }

            $userData = [
                $requestData['email'],
            ];

            $zxcvbn = new Zxcvbn();

            $weak = $zxcvbn->passwordStrength($requestData['password'], $userData);
            $passwordCheckStatus = match ($weak['score']) {
                2 => PasswordStrength::GOOD,
                3, 4 => PasswordStrength::PERFECT,
                default => PasswordStrength::BAD,
            };

            if ($passwordCheckStatus === PasswordStrength::BAD) {
                return new Response(403, ['message'=>'weak password']);
            }

            $user = new User();
            $user->setEmail($requestData['email']);
            $user->setPassword($requestData['password']);
            $user->save();

            $response = [
                'user_id' => $user->getId(),
                'password' => $passwordCheckStatus,
            ];

            return new Response(200, $response);

        } catch (InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());

            return new Response(400);
        }
    }

    public static function feed(array $requestData): Response
    {
        try {
            ArrayValidator::validateKeysOnEmpty(['access_token'], $requestData);
            $key = (string)getenv('APP_KEY');
            JWT::decode($requestData['access_token'], new Key($key, 'HS256'));
            return new Response(200);
        } catch (InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());
            return new Response(400, ['message' => $exception->getMessage()]);
        } catch (DomainException $exception) {
            Logger::error($exception->getTrace());
            return new Response(401, ['message' => 'unauthorized']);
        }
    }
}
