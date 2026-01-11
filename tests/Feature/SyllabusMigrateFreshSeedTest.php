<?php

use Symfony\Component\Process\Process;

it('migrates fresh and seeds successfully', function () {
    $process = new Process(
        ['php', 'artisan', 'migrate:fresh', '--seed', '--env=testing'],
        base_path(),
        [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ]
    );

    $process->setTimeout(120);
    $process->run();

    if (! $process->isSuccessful()) {
        throw new \RuntimeException(trim($process->getErrorOutput()."\n".$process->getOutput()));
    }

    expect($process->getExitCode())->toBe(0);
});
