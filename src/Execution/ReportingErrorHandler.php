<?php

namespace Nuwave\Lighthouse\Execution;

use Closure;
use GraphQL\Error\Error;
use Illuminate\Contracts\Debug\ExceptionHandler;

/**
 * Report errors through the default exception handler configured in Laravel.
 */
class ReportingErrorHandler implements ErrorHandler
{
    /**
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $exceptionHandler;

    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    public function __invoke(?Error $error, Closure $next): ?array
    {
        if ($error === null) {
            return $next(null);
        }

        // Client-safe errors are assumed to be something that a client can handle
        // or is expected to happen, e.g. wrong syntax, authentication or validation
        if ($error->isClientSafe()) {
            return $next($error);
        }

        $this->exceptionHandler->report(
            // @phpstan-ignore-next-line TODO remove when supporting Laravel 7 and upwards
            $error->getPrevious()
        );

        return $next($error);
    }
}
