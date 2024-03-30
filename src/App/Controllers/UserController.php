<?php

declare(strict_types=1);

namespace App\Controllers;

use InvalidArgumentException;
use App\Core\NotFoundResponse;
use App\Core\Response;
use App\Helpers\ArrayValidator;
use App\Models\User;

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
