[![Version](https://poser.pugx.org/phputil/cors/v?style=flat-square)](https://packagist.org/packages/phputil/cors)
![Build](https://github.com/thiagodp/cors/actions/workflows/ci.yml/badge.svg?style=flat)
[![License](https://poser.pugx.org/phputil/cors/license?style=flat-square)](https://packagist.org/packages/phputil/cors)

# phputil/cors

> 🔌 CORS middleware for [phputil/router](https://github.com/thiagodp/router)

- Unit-tested ✔
- Well-documented 📖
- Syntax compatible with [expressjs/cors](https://github.com/expressjs/cors) 🎯

## Installation

> Requires phputil/router **v0.2.14+**

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

### `origin: bool|string|string[]`
- Configures the response header [`Access-Control-Allow-Origin`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin), which indicates the allowed origin.
- `true`, **the default value**, reflects the `Origin` request header - that is, it **allows any origin**.
- `false` makes it to return `'*'` as the header value.
- A non-empty `string` (e.g. `'https://example.com'`) restricts the origin to the defined value. An origin must contain the protocol (e.g. `http`/`https`), and the port (e.g. `:8080`) when the port is different from the default for the protocol (`80` for http, `443` for https).
- A non-empty `array` indicates the list of allowed origins.
- When the `Origin` request header _is not sent_ and the option `origin` is `true`, it will return `*` - aiming to accept any origin (which may not work on httpS or using credentials). Other options will block the request.
- Using `*` will probably not work when using credentials or httpS. Therefore, make sure to include the allowed origins.

Note: The status code returned for an origin that is not in the allowed list is `403` (Forbidden).

### `credentials: bool`
- Configures the response header [`Access-Control-Allow-Credentials`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials).
- `true`, **the default value**, makes it to include the header.
- `false` makes it to omit the header.
- This option is needed if your application uses cookies or some kind of authentication header.
- _Bonus tip_: If you are using `fetch()` in your front-end (JavaScript), make sure to set `credentials: 'include'` (when cross-origin) or `credentials: 'same-origin` to your request options.

### `methods: string|string[]`
- Configures the response header [`Access-Control-Allow-Methods`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods).
- The **default value** is `GET,HEAD,OPTIONS,POST,PUT,DELETE,PATCH` when the request header `Access-Control-Request-Method` is _not_ defined.
- When the request header `Access-Control-Request-Method` is defined, the response header `Access-Control-Allow-Methods` will return the received method, unless the option `methods` is defined.
- HTTP methods in a `string` must be separated by **comma**.

### `allowedHeaders: string|string[]`
- Configures the response header [`Access-Control-Allow-Headers`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers).
- The **default value** is `'*'`, meaning to accept any request header.
- The value `*` only counts as a special wildcard value for requests without credentials (e.g. cookies, authorization headers). Therefore, change it if your application needs credentials.
- HTTP headers in a `string` must be separated by **comma**.

### `exposedHeaders: string|string[]`
- Configures the response header [`Access-Control-Expose-Headers`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers).
- The **default value** is `''` (empty string), meaning to not include the header.
- HTTP headers in a `string` must be separated by **comma**.
- If your application needs credentials (e.g. cookies, authentication headers), you probably should configure it.

### `maxAge: int|null`
- Configures the response header [`Access-Control-Max-Age`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age).
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
