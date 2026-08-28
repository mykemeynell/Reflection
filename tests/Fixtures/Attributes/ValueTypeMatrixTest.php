<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;
use stdClass;

final readonly class ValueTypeMatrixTest
{
    public mixed $callable;

    public function __construct(
        #[Value([1])]
        public array $array,
        #[Value(true)]
        public bool $boolean,
        #[Value('strlen')]
        callable $callable,
        #[Value(false)]
        public false $false,
        #[Value(1)]
        public float $float,
        #[Value([1])]
        public iterable $iterable,
        #[Value('anything')]
        public mixed $mixed,
        #[Value(new stdClass)]
        public object $object,
        #[Value('ten')]
        public string $string,
        #[Value(true)]
        public true $true,
        #[Value(null)]
        public null $null,
    ) {
        $this->callable = $callable;
    }
}
