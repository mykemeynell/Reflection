# Reflector

[![CI/CD (main)](https://img.shields.io/github/actions/workflow/status/mykemeynell/reflector/ci-cd.yml?label=stable%20(main)&branch=main)](https://github.com/mykemeynell/Reflector/actions/workflows/ci-cd.yml)
[![CI/CD (dev)](https://img.shields.io/github/actions/workflow/status/mykemeynell/reflector/ci-cd.yml?label=dev&branch=dev)](https://github.com/mykemeynell/Reflector/actions/workflows/ci-cd.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://www.php.net)
[![PSR-11 Compliant](https://img.shields.io/badge/PSR--11-Compliant-green.svg)](https://www.php-fig.org/psr/psr-11/)
[![License](https://img.shields.io/badge/License-MIT-lightgrey.svg)](LICENSE)

Reflector is a dependency injection container for PHP powered by auto-wiring and reflection.

It supports auto-wiring, contextual bindings, singletons, runtime parameter overrides, circular dependency detection, and PSR-11 compliance.

---

## Features

- **Auto-Wiring**: Resolves class constructor dependencies automatically using reflection.
- **PSR-11 Compliance**: Implements `Psr\Container\ContainerInterface` (`get` and `has`).
- **Contextual Binding**: Configure different implementations for specific classes using `when()->needs()->give()`.
- **Singletons**: Share instances across multiple resolutions.
- **Parameter Overrides**: Pass runtime parameters as associative arrays or named arguments.
- **Circular Dependency Detection**: Detects recursive dependency chains and throws descriptive exceptions.
- **Helper Function**: Global `app()` helper for quick access and resolution.

---

## Requirements

- PHP 8.3 or higher
- `psr/container ^2.0`

---

## Installation

```bash
composer require mykemeynell/reflection
```

---

## Quick Start

```php
use mykemeynell\Reflection\Application\Container;

$container = new Container();

class Database {}

class UserRepository {
    public function __construct(public Database $db) {}
}

$userRepository = $container->make(UserRepository::class);
```

---

## Usage

### Auto-Wiring

Classes with type-hinted constructor dependencies resolve without manual configuration:

```php
class Logger {}

class OrderService {
    public function __construct(public Logger $logger) {}
}

$service = $container->make(OrderService::class);
```

Default parameter values are used when no argument is supplied:

```php
class HttpClient {
    public function __construct(public int $timeout = 30) {}
}

$client = $container->make(HttpClient::class); // $client->timeout === 30
```

---

### Bindings

#### Interface to Implementation

```php
$container->bind(MailerInterface::class, SmtpMailer::class);
$mailer = $container->make(MailerInterface::class);
```

#### Closure Factory

```php
$container->bind(MailerInterface::class, function (Container $app, array $params = []) {
    return new SmtpMailer($params['key'] ?? 'default');
});

$mailer = $container->make(MailerInterface::class, ['key' => 'custom-key']);
```

---

### Singletons & Shared Instances

Register a singleton to return the same instance on subsequent resolutions:

```php
$container->singleton(Database::class);

$db1 = $container->make(Database::class);
$db2 = $container->make(Database::class);
// $db1 === $db2
```

Register an existing instance:

```php
$config = new AppConfig();
$container->instance(AppConfig::class, $config);
```

---

### Contextual Bindings

Inject different implementations based on the consumer class:

```php
$container->when(DirectDispatcher::class)
    ->needs(TransportInterface::class)
    ->give(HttpTransport::class);

$container->when(CustomerDispatcher::class, OutletDispatcher::class)
    ->needs(TransportInterface::class)
    ->give(QueueTransport::class);
```

`give()` also accepts a closure or a concrete instance:

```php
$container->when(ReportGenerator::class)
    ->needs(StorageInterface::class)
    ->give(fn (Container $app) => new S3Storage('bucket-name'));
```

---

### Parameter Overrides

Override constructor parameters during resolution:

```php
class ApiService {
    public function __construct(
        public HttpClient $client,
        public string $apiKey,
        public int $timeout = 30
    ) {}
}

$service = $container->make(ApiService::class, [
    'apiKey' => 'my-token',
    'timeout' => 60,
]);
```

---

### Circular Dependency Detection

Circular dependencies are detected automatically during resolution:

```php
class ServiceA {
    public function __construct(public ServiceB $b) {}
}

class ServiceB {
    public function __construct(public ServiceA $a) {}
}

$container->make(ServiceA::class);
// Throws ContainerException: Circular dependency detected while resolving [ServiceA]: ServiceA -> ServiceB -> ServiceA.
```

---

### PSR-11 Compliance

Reflector implements `Psr\Container\ContainerInterface`:

```php
use Psr\Container\ContainerInterface;

function bootstrap(ContainerInterface $container): void {
    if ($container->has(Router::class)) {
        $router = $container->get(Router::class);
    }
}
```

PSR-11 exception classes:
- `mykemeynell\Reflection\Exceptions\NotFoundException` (implements `Psr\Container\NotFoundExceptionInterface`)
- `mykemeynell\Reflection\Exceptions\ContainerException` (implements `Psr\Container\ContainerExceptionInterface`)
- `mykemeynell\Reflection\Exceptions\DependencyNotSpecifiedException` (implements `Psr\Container\ContainerExceptionInterface`)

---

### Helper Function

Import the `app` helper function:

```php
use function mykemeynell\Reflection\Helpers\app;

// Retrieve container instance
$container = app();

// Resolve service
$mailer = app(MailerInterface::class);

// Resolve with parameters
$service = app(ApiService::class, apiKey: 'token', timeout: 45);

// Resolve closure
$result = app(fn (Container $app) => $app->make(Logger::class));
```

---

## Testing

Run tests:

```bash
composer test
```

Check code style:

```bash
composer lint:check
```

Format code style:

```bash
composer lint
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

## Author

[Myke Meynell](https://github.com/mykemeynell) ([hi@myke.codes](mailto:hi@myke.codes))
