<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Core\NotFoundResponse;
use Core\Response;

class UserController
{
    public static function authorize(int $id): void
    {
        $User = User::getById($id);
        if ($User === null) {
            echo NotFoundResponse::create();

            return;
        }
    }

    public static function register(array $requestData): void
    {

    }

    public static function feed(): void
    {
        echo new Response(200, ['hello']);
    }
}
