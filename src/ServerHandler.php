<?php

namespace ByJG\RestServer\Swagger;

use ByJG\Cache\Psr16\NoCacheEngine;
use ByJG\RestServer\HandleOutput\HtmlHandler;
use ByJG\RestServer\HandleOutput\JsonHandler;
use ByJG\RestServer\HandleOutput\XmlHandler;
use ByJG\RestServer\RoutePattern;
use ByJG\RestServer\ServerRequestHandler;
use Psr\SimpleCache\CacheInterface;

class ServerHandler extends ServerRequestHandler
{
    protected $schema;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * ServerHandler constructor.
     *
     * @param $swaggerJson
     * @param \Psr\SimpleCache\CacheInterface|null $cache
     * @throws \ByJG\RestServer\Swagger\SchemaNotFoundException
     * @throws \ByJG\RestServer\Swagger\SchemaInvalidException
     */
    public function __construct($swaggerJson, CacheInterface $cache = null)
    {
        if (!file_exists($swaggerJson)) {
            throw new SchemaNotFoundException("Schema '$swaggerJson' not found");
        }

        $this->schema = json_decode(file_get_contents($swaggerJson), true);
        if (!isset($this->schema['paths'])) {
            throw new SchemaInvalidException("Schema '$swaggerJson' is invalid");
        }

        $this->cache = $cache;
        if (is_null($cache)) {
            $this->cache = new NoCacheEngine();
        }
    }

    /**
     * @param null $routePattern
     * @param bool $outputBuffer
     * @param bool $session
     * @throws \ByJG\RestServer\Exception\ClassNotFoundException
     * @throws \ByJG\RestServer\Exception\Error404Exception
     * @throws \ByJG\RestServer\Exception\Error405Exception
     * @throws \ByJG\RestServer\Exception\Error520Exception
     * @throws \ByJG\RestServer\Exception\InvalidClassException
     * @throws \ByJG\RestServer\Swagger\OperationIdInvalidException
     */
    public function handle($routePattern = null, $outputBuffer = true, $session = true)
    {
        if (is_null($routePattern)) {
            $routePattern = $this->cache->get('SERVERHANDLERROUTES', false);
            if ($routePattern === false) {
                $routePattern = $this->generateRoutes();
                $this->cache->set('SERVERHANDLERROUTES', $routePattern);
            }
        }

        parent::handle($routePattern, $outputBuffer, $session);
    }

    /**
     * @throws \ByJG\RestServer\Swagger\OperationIdInvalidException
     */
    public function generateRoutes()
    {
        $routes = [];
        foreach ($this->schema['paths'] as $path => $methodData) {
            foreach ($methodData as $method => $properties) {
                $handler = $this->getMethodHandler($properties);
                if (!isset($properties['operationId'])) {
                    throw new OperationIdInvalidException('OperationId was not found');
                }

                $parts = explode('::', $properties['operationId']);
                if (count($parts) !== 2) {
                    throw new OperationIdInvalidException(
                        'OperationId needs to be in the format Namespace\\class::method'
                    );
                }

                $routes[] = new RoutePattern(
                    strtoupper($method),
                    $path,
                    $handler,
                    $parts[1],
                    $parts[0]
                );
            }
        }

        return $routes;
    }

    protected function getMethodHandler($properties)
    {
        $handler = JsonHandler::class;

        if (!isset($properties['produces'])) {
            return $handler;
        }

        $produces = $properties['produces'];
        if (is_array($produces)) {
            $produces = $produces[0];
        }

        switch ($produces) {
            case "text/xml":
            case "application/xml":
                $handler = XmlHandler::class;
                break;

            case "text/html":
                $handler = HtmlHandler::class;
                break;
        }

        return $handler;
    }
}
