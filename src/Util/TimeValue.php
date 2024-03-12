<?php

namespace Aternos\Rados\Util;

use FFI;
use FFI\CData;

class TimeValue
{
    public function __construct(
        protected int $seconds,
        protected int $microseconds = 0
    )
    {
    }

    /**
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * @param int $microseconds
     * @return $this
     */
    public function setMicroseconds(int $microseconds): TimeValue
    {
        $this->microseconds = $microseconds;
        return $this;
    }

    /**
     * @param int $seconds
     * @return $this
     */
    public function setSeconds(int $seconds): TimeValue
    {
        $this->seconds = $seconds;
        return $this;
    }

    /**
     * @return int
     */
    public function getMicroseconds(): int
    {
        return $this->microseconds;
    }

    /**
     * @param FFI $ffi
     * @return CData
     * @noinspection PhpUndefinedFieldInspection
     * @internal
     */
    public function createCData(FFI $ffi): CData
    {
        $data = $ffi->new("struct timeval");
        $data->tv_sec = $this->seconds;
        $data->tv_usec = $this->microseconds;
        return $data;
    }
}
