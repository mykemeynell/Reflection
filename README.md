# Reflection

[![CI/CD (main)](https://img.shields.io/github/actions/workflow/status/mykemeynell/reflection/tests.yml?label=stable%20(main)&branch=main)](https://github.com/mykemeynell/reflection/actions/workflows/tests.yml)
[![CI/CD (dev)](https://img.shields.io/github/actions/workflow/status/mykemeynell/reflection/tests.yml?label=dev&branch=dev)](https://github.com/mykemeynell/reflection/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://www.php.net)
[![PSR-11 Compliant](https://img.shields.io/badge/PSR--11-Compliant-green.svg)](https://www.php-fig.org/psr/psr-11/)
[![License](https://img.shields.io/badge/License-MIT-lightgrey.svg)](LICENSE)

Reflection is a dependency injection container for PHP powered by auto-wiring and reflection.

It supports auto-wiring, contextual bindings, singletons, attribute-driven resolution, runtime parameter overrides, circular dependency detection, and PSR-11 compliance.

---

## Features

- **Auto-Wiring**: Resolves class constructor dependencies automatically using reflection.
- **PSR-11 Compliance**: Implements `Psr\Container\ContainerInterface` (`get` and `has`).
- **Contextual Binding**: Configure different implementations for specific classes using `when()->needs()->give()`.
- **Singletons**: Share instances across multiple resolutions.
- **Resolution Attributes**: Declare injection targets, scalar values, and singleton lifecycles directly on classes.
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

Register a singleton to return the same instance on later resolutions:

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

Override constructor parameters using named arguments:

```php
class ApiService {
    public function __construct(
        public HttpClient $client,
        public string $apiKey,
        public int $timeout = 30
    ) {}
}

$service = $container->make(
    ApiService::class,
    apiKey: 'my-token',
    timeout: 60,
);
```

Associative parameter arrays remain supported:

```php
$service = $container->make(ApiService::class, [
    'apiKey' => 'my-token',
    'timeout' => 60,
]);
```

The previous named parameter-array form remains supported for compatibility:

```php
$service = $container->make(
    ApiService::class,
    parameters: ['apiKey' => 'my-token', 'timeout' => 60],
);
```

Named arguments can also be used through the container returned by `app()`:

```php
$service = app()->make(ApiService::class, timeout: 60);
```

---

### Resolution Attributes

Use `Inject` when a constructor parameter needs a specific resolution target:

```php
use mykemeynell\Reflection\Attributes\Inject;

final readonly class ReportService
{
    public function __construct(
        #[Inject(S3Storage::class)]
        public StorageInterface $storage,
    ) {}
}
```

An ordinary `Inject` is a fallback after contextual and global bindings. Add `Override` when the injection point must take precedence over those registrations:

```php
use mykemeynell\Reflection\Attributes\Inject;
use mykemeynell\Reflection\Attributes\Override;

final readonly class ReportService
{
    public function __construct(
        #[Inject(S3Storage::class), Override]
        public StorageInterface $storage,
    ) {}
}
```

Runtime arguments always take precedence, including when `Override` is present.

Use `Value` for scalar constructor configuration:

```php
use mykemeynell\Reflection\Attributes\Value;

final readonly class HttpClient
{
    public function __construct(
        #[Value(3)]
        public int $retries,
        #[Value(30)]
        public int $timeout,
    ) {}
}
```

Value attributes are checked against the declared parameter type. A runtime argument overrides the attribute value.

Use `Singleton` to share an automatically resolved concrete class:

```php
use mykemeynell\Reflection\Attributes\Singleton;

#[Singleton]
final class DatabaseConnection {}
```

An explicit `instance()`, `singleton()`, or transient `bind()` registration overrides the class attribute.

#### Resolution Precedence

Object dependencies use this order:

1. Runtime argument
2. Contextual binding
3. Registered instance or global binding
4. `Inject`
5. Automatic concrete-class resolution
6. Constructor default

For parameters marked with both `Inject` and `Override`, `Inject` moves directly below the runtime argument. Scalar parameters use runtime argument, `Value`, then constructor default. Lifecycle selection uses registered instance, explicit singleton, explicit transient binding, `Singleton`, then transient automatic resolution.

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

Reflection implements `Psr\Container\ContainerInterface`:

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

Install the PHP 8.4 static-analysis toolchain and run PHPStan:

```bash
composer analyze:install
composer analyze
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

## Author

[Myke Meynell](https://github.com/mykemeynell) ([hi@myke.codes](mailto:hi@myke.codes))
