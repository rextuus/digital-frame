<?php

declare(strict_types=1);

namespace App\Service\Util;

use Throwable;

use function Sentry\captureException;

class SentryService
{
    public function captureException(Throwable $exception): void
    {
        captureException($exception);
    }
}
