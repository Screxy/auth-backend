<?php

declare(strict_types=1);

namespace App\Controllers;

use InvalidArgumentException;
use Task\App\Core\NotFoundResponse;
use Task\App\Core\Response;
use Task\App\Helpers\ArrayValidator;
use Task\App\Models\Product;

class ProductController
{
    public static function get(int $id): void
    {
        $product = Product::getById($id);
        if ($product === null) {
            echo NotFoundResponse::create();

            return;
        }

        echo new Response(200, $product->toArray());
    }

    public static function add(array $requestData): void
    {
        try {
            ArrayValidator::validateKeysOnEmpty(Product::REQUIRED_FIELDS, $requestData);
            $validatedData = Product::validate($requestData);
            $product = new Product();
            $product->setName($validatedData['name']);
            $product->setSupplierEmail($validatedData['supplierEmail']);
            $product->setCount($validatedData['count']);
            $product->setPrice($validatedData['price']);
            $product->save();

            echo new Response(201, $product->toArray());
        } catch (InvalidArgumentException $exception) {
            $message = json_decode($exception->getMessage(), true) ?? ['message' => $exception->getMessage()];
            echo new Response($exception->getCode(),
                $message
            );
        }
    }

    public static function delete(int $id): void
    {
        $product = Product::getById($id);
        if ($product === null) {
            echo NotFoundResponse::create();;
            return;
        }
        $product->destroy();
        echo new Response(204, []);
    }

    public static function edit(array $requestData): void
    {
        try {
            ArrayValidator::validateKeysOnEmpty(array_merge(Product::REQUIRED_FIELDS, ['id']), $requestData);
            $validatedData = Product::validate($requestData, true);

            $product = Product::getById($validatedData['id']);

            if ($product === null) {
                echo NotFoundResponse::create();

                return;
            }

            $product->setName($validatedData['name']);
            $product->setSupplierEmail($validatedData['supplierEmail']);
            $product->setCount($validatedData['count']);
            $product->setPrice($validatedData['price']);
            $product->save();

            echo new Response(200, $product->toArray());
        } catch (InvalidArgumentException $exception) {
            $message = json_decode($exception->getMessage(), true) ?? ['message' => $exception->getMessage()];
            echo new Response($exception->getCode(),
                $message
            );
        }
    }

    public static function getAll(): void
    {
        $products = Product::findAll();
        $response = [];
        if ($products === null) {
            echo NotFoundResponse::create();
            return;
        }
        foreach ($products as $product) {
            $response[] = $product->toArray();
        }

        echo new Response(200, $response);
    }
}
