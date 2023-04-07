[![Version](https://poser.pugx.org/phputil/cors/v?style=flat-square)](https://packagist.org/packages/phputil/cors)
[![License](https://poser.pugx.org/phputil/cors/license?style=flat-square)](https://packagist.org/packages/phputil/cors)

# phputil/cors

> 🔌 CORS middleware for [phputil/router](https://github.com/thiagodp/router)

- Unit-tested ✔
- Well-documented 📖
- Syntax compatible with [expressjs/cors](https://github.com/expressjs/cors) 🎯

## Installation

> Requires phputil/router **v0.2.11+**

```bash
composer require phputil/cors
```

## Usage

### Basic usage

```php
require_once 'vendor/autoload.php';
use phputil\router\Router;
use function phputil\cors\cors; // Step 1: Declare the namespace usage for the function.
$app = new Router();

$app->use( cors() ); // Step 2: Invoke the function to use it as a middleware.

$app->get( '/', function( $req, $res ) {
    $res->send( 'Hello' );
} );
$app->listen();
```

## API

```php
function cors( array|CorOptions $options ): callable;
```
`$options` can be an **array** or an **object** from the class `CorOptions`. All its keys/attributes are **optional**:

### `origin`
- Configures the response header `Access-Control-Allow-Origin`, which indicates the allowed origin.
- Allowed types: `bool`, `string`, `array`.
- `true`, **the default value**, reflects the `Origin` request header - that is, it **allows any origin**.
- `false` makes it to return `'*'` as the header value.
- A non-empty `string` value (e.g. `'mydomain.com'`) restricts the `Origin` to the defined value.
- A non-empty `array` value indicates that `Origin` values are allowed.
- When the `Origin` request header _is not sent_ and the option `origin` is `true`, it
  will return `*` - aiming to accept any origin. Other options will block the request.
- Using `*` may not work when using credentials or using httpS. Prefer sending the request header `Origin` whenever possible.

### `credentials`
- Configures the response header `Access-Control-Allow-Credentials`.
- Allowed types: `bool`.
- `true`, **the default value**, makes it to include the header.
- `false` makes it to omit the header.
- This header is important if your application uses cookies or some kind of authentication header.

### `methods`
- Configures the response header `Access-Control-Allow-Methods`.
- Allowed types: `string`, `array`.
- The **default value** is `GET,HEAD,OPTIONS,POST,PUT,DELETE,PATCH`.
- HTTP methods in a `string` must be separated by **comma**.

### `allowedHeaders`
- Configures the response header `Access-Control-Allow-Headers`.
- Allowed types: `string`, `array`.
- The **default value** is `'*'`, meaning to accept any request header.
- HTTP headers in a `string` must be separated by **comma**.

### `exposedHeaders`
- Configures the response header `Access-Control-Expose-Headers`.
- Allowed types: `string`, `array`.
- The **default value** is `''` (empty string), meaning to not include the header.
- HTTP headers in a `string` must be separated by **comma**.

### `maxAge`
- Configures the response header `Access-Control-Max-Age`.
- Allowed types: `int`, `null`.
- The **default value** is `null`, meaning to not include the header.
- An `int` value means the number of seconds that a preflight request can be cached (by the browser).

## Example

Using an array:

```php
$options = [
    'origin' => 'mydomain.com',
    'methods' => 'GET,POST'
];

$app->use( cors( $options ) );
```

Using the class `CorOptions`, that has nestable builder methods with the prefix `with`:

```php
use phputil\cors\CorsOptions; // Needed

$options = ( new CorsOptions() )
    ->withOrigin( 'mydomain.com' )
    ->withMethods( 'GET,POST' );

$app->use( cors( $options ) );
```


## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
