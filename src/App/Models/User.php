<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

class User extends ActiveRecordEntity
{
    protected string $email;
    protected string $supplierEmail;
    protected float $accessToken;

    public const array REQUIRED_FIELDS = ['name', 'supplierEmail', 'price', 'count'];

    protected static function getTableName(): string
    {
        return 'product';
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getSupplierEmail(): string
    {
        return $this->supplierEmail;
    }

    public function setSupplierEmail(string $supplierEmail): void
    {
        $this->supplierEmail = $supplierEmail;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->email,
            'supplierEmail' => $this->supplierEmail,
            'count' => $this->count,
            'price' => $this->price,
        ];
    }

    /**
     * Принимает массив данных и проверяет
     * данные на основе правил проверки.
     *
     *
     * @param array $data
     * @param bool|null $withId
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public static function validate(array $data, ?bool $withId = false): array
    {
        $errors = [];
        $validatedData = [];

        $validationRules = [
            'name' => 'string',
            'supplierEmail' => 'email',
            'count' => 'int',
            'price' => 'float',
        ];
        if ($withId) {
            $validationRules['id'] ='int';
        }

        foreach ($validationRules as $field => $rule) {
            if (isset($data[$field])) {
                $isValid = self::validateField($data[$field], $rule);
                if ($isValid !== true) {
                    $errors[$field] = $isValid;
                } else {
                    $validatedData[$field] = $data[$field];
                }
            } else {
                $errors[$field] = 'Field is missing';
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(json_encode($errors), 400);
        }

        return $validatedData;
    }

    private static function validateField($value, $rule): bool|string
    {
        return match ($rule) {
            'id', 'int' => is_int($value) ? true : 'Field must be an integer',
            'string' => is_string($value) ? true : 'Field must be a string',
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? true : 'Invalid email',
            'float' => is_numeric($value) ? true : 'Field must be a number',
            default => 'Invalid validation rule',
        };
    }
}
