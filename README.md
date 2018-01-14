# PHP Swagger Rest Server
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/restserver-swagger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/restserver-swagger/?branch=master)
[![Build Status](https://travis-ci.org/byjg/restserver-swagger.svg?branch=master)](https://travis-ci.org/byjg/restserver-swagger)

## Description

Enable to create RESTFull services with strong model schema. 
The routes are automatically created from a swagger.json file.

## Installation

```bash
composer require "byjg/restserver-swagger=1.0.*"
```

## Basic Usage

First you need to generate a swagger.json file and the "operationId" must have the 
`Namespace\\Class::method` like the example below:

```json
{
  ...
  "paths": {
    "/pet": {
      "post": {
        "summary": "Add a new pet to the store",
        "description": "",
        "operationId": "PetStore\\Pet::addPet"
      }
    }
  }
  ...
}
```

Note: If you are using the [zircote/swagger-php](https://github.com/zircote/swagger-php) 
for auto generate your JSON file from PHPDocs comments, since the version 2.0.14 it can
generate the proper "operationId" for you. Just run on command line:

```bash
swagger --operationid
```

After you have the proper swagger.json you need to add it to your project:

```bash
composer require "byjg/restserver-swagger=1.0.*"
```

and create a `app.php` file:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$restServer = new \ByJG\RestServer\Swagger\ServerHandler(__DIR__ . '/swagger.json');

$restServer->handle();
```

### Caching the Routes

It is possible to cache the route by adding any PSR-16 instance on the second parameter of the constructor:

```php
<?php
$restServer = new \ByJG\RestServer\Swagger\ServerHandler(
    __DIR__ . '/swagger.json',
    new \ByJG\Cache\Psr16\FileSystemCacheEngine()
);
```

## More Informations

This project is based on [byjg/restserver](https://github.com/byjg/restserver). 

There you can find more informations.

----
[Open source ByJG](http://opensource.byjg.com)
