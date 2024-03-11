<?php

namespace Aternos\Rados\Cluster\Pool\Object;

class ObjectStat
{
    /**
     * @param int $size
     * @param int $modifiedTime
     */
    public function __construct(
        protected int $size,
        protected int $modifiedTime
    )
    {
    }

    /**
     * @return int
     */
    public function getModifiedTime(): int
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
