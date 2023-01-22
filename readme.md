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

### Basic usage

```php
require_once 'vendor/autoload.php';
use phputil\router\Router;
use function phputil\cors\cors; // <<< 1. Declare the function namespace
$app = new Router();

// It will enable CORS for ALL origins (*)
$app->use( cors() ); // <<< 2. Invoke the function to use it as a middleware

$app->get( '/', function( $req, $res ) {
    $res->send( 'Hello' );
} );
$app->listen();
```

## API

This middleware is inspired by Troy Goode's [CORS Router for ExpressJS](https://github.com/expressjs/cors) and it aims to have the same options.

```php
function cors( array|CorOptions $options ): callable;
```

`$options` can be an **array** or an **object** from the class `CorOptions`. All its keys/attributes are **optional**:

| Key/Attribute          | Type                | Default value                      | Corresponding CORS Header          |
|------------------------|---------------------|------------------------------------|------------------------------------|
| `origin`               | _string_ or _array_ | `'*'`                              | `Access-Control-Allow-Origin`      |
| `methods`              | _string_ or _array_ | `'GET,HEAD,PUT,PATCH,POST,DELETE'` | `Access-Control-Allow-Methods`     |
| `credentials`          | _bool_              | `false`                            | `Access-Control-Allow-Credentials` |
| `allowedHeaders`       | _string_ or _array_ | `*`                                | `Access-Control-Allow-Headers`     |
| `exposedHeaders`       | _string_ or _array_ | `*`                                | `Access-Control-Expose-Headers`    |
| `maxAge`               | _int_ or `null`     | `null`                             | `Access-Control-Max-Age`           |
| `preflightContinue`    | _bool_              | `false`                            | N/A                                |
| `optionsSuccessStatus` | _int_               | `204`                              | N/A                                |

Example:
```php
$options = [
    'origin' => 'https://example.com',
    'methods' => 'GET,POST'
];

$app->use( cors( $options ) );
```

Class `CorOptions` has nestable builder methods with the prefix `with`. Example:

```php
use phputil\cors\CorsOptions;

$options = ( new CorsOptions() )
    ->withOrigin( 'https://example.com' )
    ->withMethods( 'GET,POST' );

$app->use( cors( $options ) );
```


## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
