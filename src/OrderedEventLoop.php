<?php

declare(strict_types=1);

namespace App;

final class OrderedEventLoop
{
    /**
     * @var OrderedEventLoop|null
     */
    private static ?self $instance = null;

    /**
     * @param \SplObjectStorage<\Fiber, mixed> $queue
     * @throws \Throwable
     */
    private function __construct(
        private readonly \SplObjectStorage $queue = new \SplObjectStorage(),
    ) {
        \register_shutdown_function(function () {
            $this->run();
        });
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * @template TResume of mixed
     * @template TReturn of mixed
     *
     * @param \Fiber<null, TResume, TReturn, null> $task
     * @return TResume|TReturn
     * @throws \Throwable
     */
    private function tick(\Fiber $task): mixed
    {
        return match (false) {
            $task->isStarted() => $task->start(),
            $task->isTerminated() => $task->resume(),
            default => $this->cancel($task),
        };
    }

    /**
     * @param \Fiber $fiber
     * @return mixed
     */
    public function cancel(\Fiber $fiber): mixed
    {
        $this->queue->detach($fiber);

        if ($fiber->isTerminated()) {
            return $fiber->getReturn();
        }

        return null;
    }

    /**
     * @param \Fiber $fiber
     * @return \Fiber
     */
    public function attach(\Fiber $fiber): \Fiber
    {
        $this->queue->attach($fiber);

        return $fiber;
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        do {
            foreach ($this->queue as $task) {
                $result = $this->tick($task);

                if ($result instanceof \Fiber) {
                    $this->attach($result);
                }

                \Fiber::getCurrent() && \Fiber::suspend($result);
            }
        } while ($this->queue->count() !== 0);
    }
}