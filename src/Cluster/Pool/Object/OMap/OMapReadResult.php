<?php

namespace Aternos\Rados\Cluster\Pool\Object\OMap;

class OMapReadResult
{
    /**
     * @param OMapIterator $iterator
     * @param bool $hasMoreEntries
     */
    public function __construct(
        protected OMapIterator $iterator,
        protected bool $hasMoreEntries
    )
    {
    }

    /**
     * @return OMapIterator
     */
    public function getIterator(): OMapIterator
    {
        return $this->iterator;
    }

    /**
     * @return bool
     */
    public function hasMore(): bool
    {
        return $this->hasMoreEntries;
    }
}
