[![Version](https://poser.pugx.org/phputil/cors/v?style=flat-square)](https://packagist.org/packages/phputil/cors)
[![License](https://poser.pugx.org/phputil/cors/license?style=flat-square)](https://packagist.org/packages/phputil/cors)

# phputil/cors

> 🔌 CORS middleware for phputil/router

_Warning: This library is under development. Do not use it in production yet._

## Installation

```bash
composer require phputil/cors
```

## Usage in phputil\router

```php
// ...
use function phputil\cors\cors;
// ...
$app->use( cors() );
```

## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
