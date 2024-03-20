<?php

namespace Aternos\Rados\Cluster\Pool\Object;

use Aternos\Rados\Util\TimeSpec;

class ObjectStat
{
    /**
     * @param int $size
     * @param TimeSpec $modifiedTime
     */
    public function __construct(
        protected int $size,
        protected TimeSpec $modifiedTime
    )
    {
    }

    /**
     * @return TimeSpec
     */
    public function getModifiedTime(): TimeSpec
    {
        return $this->modifiedTime;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
