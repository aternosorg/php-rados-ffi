<?php

namespace Aternos\Rados\Util;

use FFI;
use FFI\CData;

class TimeSpec
{
    /**
     * @param CData $data
     * @return static
     * @noinspection PhpUndefinedFieldInspection
     * @internal Use new TimeSpec() instead
     */
    public static function fromCData(CData $data): static
    {
        return new static($data->tv_sec, $data->tv_nsec);
    }

    /**
     * @param int $seconds
     * @param int $nanoseconds
     */
    public function __construct(
        protected int $seconds,
        protected int $nanoseconds = 0
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
     * @return int
     */
    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * @param int $seconds
     * @return $this
     */
    public function setSeconds(int $seconds): TimeSpec
    {
        $this->seconds = $seconds;
        return $this;
    }

    /**
     * @param int $nanoseconds
     * @return $this
     */
    public function setNanoseconds(int $nanoseconds): TimeSpec
    {
        $this->nanoseconds = $nanoseconds;
        return $this;
    }

    /**
     * @param FFI $ffi
     * @return CData
     * @noinspection PhpUndefinedFieldInspection
     * @internal
     */
    public function createCData(FFI $ffi): CData
    {
        $data = $ffi->new("struct timespec");
        $data->tv_sec = $this->seconds;
        $data->tv_nsec = $this->nanoseconds;
        return $data;
    }
}
