<?php

declare(strict_types=1);

namespace Core;

class Router
{
    private const string METHOD_GET = 'GET';
    private const string METHOD_POST = 'POST';
    private const string METHOD_PATCH = 'PATCH';
    private const string METHOD_DELETE = 'DELETE';
    private array $handlers;
    /**
     * @var callable
     */
    private $notFoundHandler;

    public function get(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_GET, $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_POST, $path, $handler);
    }

    public function patch(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_PATCH, $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_DELETE, $path, $handler);
    }

    public function run(string $method, string $path): void
    {
        $requestUri = parse_url($path);
        $requestPath = $requestUri['path'];
        $callback = null;
        $params = [];

        /** @var Handler $handler */
        foreach ($this->handlers as $handler) {
            $pattern = $this->preparePathPattern($handler->getPath());
            if (preg_match($pattern, $requestPath, $matches) && $method === $handler->getMethod()) {
                $callback = $handler->getCallback();
                array_shift($matches);
                if (!empty($matches)) {
                    $params = $matches;
                }
                break;
            }
        }

        if (null === $callback) {
            $callback = $this->notFoundHandler;
        }

        if (is_string($callback)) {
            $parts = explode('::', $callback);
            $class = $parts[0];
            $handler = new $class;

            $method = $parts[1];
            $callback = [$handler, $method];
        }

        if ($_SERVER['REQUEST_METHOD'] !== self::METHOD_GET) {
            $body = file_get_contents('php://input');
            $params[] = json_decode($body, true);
        }

        call_user_func_array($callback, $params);
    }

    private function preparePathPattern(string $path): string
    {
        $pattern = preg_replace('#/:\w+#', '/([^/]+)', $path);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '\/?$/';
    }


    public function addNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }
}
