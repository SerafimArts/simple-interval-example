<?php

declare(strict_types=1);

namespace App;

final class PeriodicTimer
{
    private float $updatedAt = 0.0;
    private ?\Fiber $process = null;

    public function __construct(
        private readonly \Closure $handler,
        private readonly int|float $interval = 1.0,
    ) {
    }

    public function cancel(): void
    {
        if ($this->process === null) {
            return;
        }

        Task::cancel($this->process);
        $this->process = null;
    }

    public function run(): \Fiber
    {
        if ($this->process !== null) {
            return $this->process;
        }

        return $this->process = Task::run(function () {
            while (true) {
                $now = \microtime(true);

                // Timer initialization
                if ($this->updatedAt === 0.0) {
                    $this->updatedAt = $now;
                    continue;
                }

                if ($this->updatedAt + $this->interval <= $now) {
                    $this->updatedAt = $now;

                    ($this->handler)();
                }

                \Fiber::suspend($now);
            }
        });
    }
}
