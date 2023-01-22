[![Version](https://poser.pugx.org/phputil/cors/v?style=flat-square)](https://packagist.org/packages/phputil/cors)
[![License](https://poser.pugx.org/phputil/cors/license?style=flat-square)](https://packagist.org/packages/phputil/cors)

# phputil/cors

> 🔌 CORS middleware for [phputil/router](https://github.com/thiagodp/router)

_Warning: This library is under development. Do not use it in production yet._

## Installation

> Requires phputil/router **v0.2.6+**

```bash
composer require phputil/cors
```

## Usage

```php
require_once 'vendor/autoload.php';
use phputil\router\Router;
use function phputil\cors\cors; // <<< 1. Declare the function namespace

$app = new Router();
$app->use( cors() ); // <<< 2. Invoke the function to use it as a middleware
$app->get( '/', function( $req, $res ) {
    $res->send( 'Hello' );
} );
$app->listen();
```

## API

This middleware is inspired by Troy Goode's [CORS Router for ExpressJS](https://github.com/expressjs/cors) and it aims to have the same options.

_Soon_

## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
