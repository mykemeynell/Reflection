<?php

require __DIR__.'/vendor/autoload.php';

use function \mykemeynell\Reflector\Helpers\app;

app()->bind('test', fn () => 'tested');
app()->singleton('test_singleton', fn () => 'tested');

\Symfony\Component\VarDumper\VarDumper::dump([
    app(),
    app('test'),
    app('test_singleton'),
    app()
]);
