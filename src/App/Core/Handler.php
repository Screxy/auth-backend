<?php

declare(strict_types=1);

namespace App\Core;

class Handler
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private string          $callback
    )
    {
        $this->callback = $callback;
    }

    public static function create(string $method, string $path, string $callback): self
    {
        return new self ($method, $path, $callback);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getCallback(): string
    {
        return $this->callback;
    }
}
