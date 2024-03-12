<?php

namespace Aternos\Rados\Exception;

use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Generated\Errno;

class RadosException extends \Exception
{
    /**
     * @param int $code
     * @return static
     */
    public static function fromErrorCode(int $code): static
    {
        if (function_exists("posix_strerror")) {
            $message = posix_strerror(-$code);
        } else {
            $message = "Rados returned error code " . $code . ": " . Errno::getErrorName(-$code);
        }
        return new static($message, $code);
    }

    /**
     * @param int $code
     * @return int
     * @throws static
     */
    public static function handle(int $code): int
    {
        if ($code < 0 && $code >= -Constants::MAX_ERRNO) {
            throw static::fromErrorCode($code);
        }
        return $code;
    }

    /**
     * @param Errno ...$errno
     * @return bool
     */
    public function is(Errno ...$errno): bool
    {
        foreach ($errno as $e) {
            if ($this->getCode() === -$e->value) {
                return true;
            }
        }
        return false;
    }
}
