<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\WrappedType;

class PoolStat extends WrappedType
{
    /**
     * Space used in bytes
     * @return int
     */
    protected function getNumBytes(): int
    {
        return $this->getData()->num_bytes;
    }

    /**
     * Space used in KB
     * @return int
     */
    protected function getNumKb(): int
    {
        return $this->getData()->num_kb;
    }

    /**
     * Number of objects in the pool
     * @return int
     */
    protected function getNumObjects(): int
    {
        return $this->getData()->num_objects;
    }

    /**
     * Number of clones of objects
     * @return int
     */
    protected function getNumObjectClones(): int
    {
        return $this->getData()->num_object_clones;
    }

    /**
     * NumObjects * numReplicas
     * @return int
     */
    protected function getNumObjectCopies(): int
    {
        return $this->getData()->num_object_copies;
    }

    /**
     * Number of objects missing on primary
     * @return int
     */
    protected function getNumObjectsMissingOnPrimary(): int
    {
        return $this->getData()->num_objects_missing_on_primary;
    }

    /**
     * Number of objects found on no OSDs
     * @return int
     */
    protected function getNumObjectsUnfound(): int
    {
        return $this->getData()->num_objects_unfound;
    }

    /**
     * Number of objects replicated fewer times than they should be
     * (but found on at least one OSD)
     * @return int
     */
    protected function getNumObjectsDegraded(): int
    {
        return $this->getData()->num_objects_degraded;
    }

    /**
     * Number of objects read
     * @return int
     */
    protected function getNumRd(): int
    {
        return $this->getData()->num_rd;
    }

    /**
     * Objects read in KB
     * @return int
     */
    protected function getNumRdKb(): int
    {
        return $this->getData()->num_rd_kb;
    }

    /**
     * Number of objects written
     * @return int
     */
    protected function getNumWr(): int
    {
        return $this->getData()->num_wr;
    }

    /**
     * Objects written in KB
     * @return int
     */
    protected function getNumWrKb(): int
    {
        return $this->getData()->num_wr_kb;
    }

    /**
     * Bytes originally provided by user
     * @return int
     */
    protected function getNumUserBytes(): int
    {
        return $this->getData()->num_user_bytes;
    }

    /**
     * Bytes passed compression
     * @return int
     */
    protected function getCompressedBytesOrig(): int
    {
        return $this->getData()->compressed_bytes_orig;
    }

    /**
     * Bytes resulted after compression
     * @return int
     */
    protected function getCompressedBytes(): int
    {
        return $this->getData()->compressed_bytes;
    }

    /**
     * Bytes allocated at storage
     * @return int
     */
    protected function getCompressedBytesAlloc(): int
    {
        return $this->getData()->compressed_bytes_alloc;
    }
}
