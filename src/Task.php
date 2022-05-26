<?php

declare(strict_types=1);

namespace App;

final class Task
{
    /**
     * @param \Closure $task
     * @return \Fiber
     */
    public static function run(\Closure $task): \Fiber
    {
        return OrderedEventLoop::getInstance()
            ->attach(new \Fiber($task));
    }

    /**
     * @param \Fiber $fiber
     * @return void
     */
    public static function cancel(\Fiber $fiber): void
    {
        OrderedEventLoop::getInstance()
            ->cancel($fiber);
    }
}