<?php

require_once __DIR__ . '/../vendor/autoload.php';

$restServer = new \ByJG\RestServer\Swagger\ServerHandler(__DIR__ . '/../tests/swagger-example.json');

$restServer->handle();
