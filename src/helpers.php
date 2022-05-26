<?php

declare(strict_types=1);

use App\PeriodicTimer;

function setInterval(\Closure $handler, int|float $interval = 1.0): PeriodicTimer
{
    $timer = new PeriodicTimer($handler, $interval);
    $timer->run();

    return $timer;
}

function cancelInterval(PeriodicTimer $timer): void
{
    $timer->cancel();
}