<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enum\PasswordStrength;
use App\Helpers\ArrayValidator;
use App\Models\User;
use Core\Logger;
use Core\NotFoundResponse;
use Core\Request;
use Core\Response;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use ZxcvbnPhp\Zxcvbn;

class UserController
{
    public static function authorize(Request $request): Response
    {
        try {
            $body = $request->getBody();
            ArrayValidator::validateKeysOnEmpty(['email', 'password'], $body);

            $user = User::getByEmail($body['email']);
            if ($user === null) {
                return NotFoundResponse::create();
            }

            $isValid = password_verify($body['password'], $user->getPassword());

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

    public static function register(Request $request): Response
    {
        try {
            $body = $request->getBody();

            ArrayValidator::validateKeysOnEmpty(['email', 'password'], $body);


            if (User::getByEmail($body['email'])) {
                return new Response(409, ['message' => 'User already exist']);
            }

            $userData = [
                $body['email'],
            ];

            $zxcvbn = new Zxcvbn();

            $weak = $zxcvbn->passwordStrength($body['password'], $userData);
            $passwordCheckStatus = match ($weak['score']) {
                2 => PasswordStrength::GOOD,
                3, 4 => PasswordStrength::PERFECT,
                default => PasswordStrength::BAD,
            };

            if ($passwordCheckStatus === PasswordStrength::BAD) {
                return new Response(403, ['message' => 'weak password']);
            }

            $user = new User();
            $user->setEmail($body['email']);
            $user->setPassword($body['password']);
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

    public static function feed(Request $request): Response
    {
        try {
            $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
            $accessToken = str_replace('Bearer ', '', $authorizationHeader);

            $key = (string)getenv('APP_KEY');

            JWT::decode($accessToken, new Key($key, 'HS256'));

            return new Response(200);
        } catch (DomainException|SignatureInvalidException|InvalidArgumentException $exception) {
            Logger::error($exception->getTrace());
            return new Response(401, ['message' => 'unauthorized']);
        }
    }

    public static function test(Request $request): void
    {
        $authorizationHeader = $request->getHeaders()['Authorization'] ?? '';
        $accessToken = str_replace('Bearer', '', $authorizationHeader);
        var_dump($accessToken);
    }
}
