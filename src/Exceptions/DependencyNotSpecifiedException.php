<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Exceptions;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

final class DependencyNotSpecifiedException extends LogicException implements ContainerExceptionInterface {}
