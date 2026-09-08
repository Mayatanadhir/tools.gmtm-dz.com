<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseService
{
    /**
     * Execute a callback within a database transaction with centralized exception handling and logging.
     *
     * @template T
     *
     * @param  (Closure(): T)  $callback
     * @return T
     *
     * @throws Throwable
     */
    protected function executeInTransaction(Closure $callback, int $attempts = 1): mixed
    {
        try {
            return DB::transaction($callback, $attempts);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] Transaction failed: %s', static::class, $e->getMessage()), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
