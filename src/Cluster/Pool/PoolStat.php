<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Aternos\Rados\Cluster\Pool;

use FFI\CData;

class PoolStat
{
    /**
     * @param CData $stat - CData of type rados_pool_stat_t
     * @return PoolStat
     */
    public static function fromStatCData(CData $stat): PoolStat
    {
        return new PoolStat(
            $stat->num_bytes,
            $stat->num_kb,
            $stat->num_objects,
            $stat->num_object_clones,
            $stat->num_object_copies,
            $stat->num_objects_missing_on_primary,
            $stat->num_objects_unfound,
            $stat->num_objects_degraded,
            $stat->num_rd,
            $stat->num_rd_kb,
            $stat->num_wr,
            $stat->num_wr_kb,
            $stat->num_user_bytes,
            $stat->compressed_bytes_orig,
            $stat->compressed_bytes,
            $stat->compressed_bytes_alloc
        );
    }

    /**
     * @param int $numBytes
     * @param int $numKb
     * @param int $numObjects
     * @param int $numObjectClones
     * @param int $numObjectCopies
     * @param int $numObjectsMissingOnPrimary
     * @param int $numObjectsUnfound
     * @param int $numObjectsDegraded
     * @param int $numRd
     * @param int $numRdKb
     * @param int $numWr
     * @param int $numWrKb
     * @param int $numUserBytes
     * @param int $compressedBytesOrig
     * @param int $compressedBytes
     * @param int $compressedBytesAlloc
     */
    public function __construct(
        protected int $numBytes,
        protected int $numKb,
        protected int $numObjects,
        protected int $numObjectClones,
        protected int $numObjectCopies,
        protected int $numObjectsMissingOnPrimary,
        protected int $numObjectsUnfound,
        protected int $numObjectsDegraded,
        protected int $numRd,
        protected int $numRdKb,
        protected int $numWr,
        protected int $numWrKb,
        protected int $numUserBytes,
        protected int $compressedBytesOrig,
        protected int $compressedBytes,
        protected int $compressedBytesAlloc,
    )
    {
    }

    /**
     * Space used in bytes
     * @return int
     */
    protected function getNumBytes(): int
    {
        return $this->numBytes;
    }

    /**
     * Space used in KB
     * @return int
     */
    protected function getNumKb(): int
    {
        return $this->numKb;
    }

    /**
     * Number of objects in the pool
     * @return int
     */
    protected function getNumObjects(): int
    {
        return $this->numObjects;
    }

    /**
     * Number of clones of objects
     * @return int
     */
    protected function getNumObjectClones(): int
    {
        return $this->numObjectClones;
    }

    /**
     * NumObjects * numReplicas
     * @return int
     */
    protected function getNumObjectCopies(): int
    {
        return $this->numObjectCopies;
    }

    /**
     * Number of objects missing on primary
     * @return int
     */
    protected function getNumObjectsMissingOnPrimary(): int
    {
        return $this->numObjectsMissingOnPrimary;
    }

    /**
     * Number of objects found on no OSDs
     * @return int
     */
    protected function getNumObjectsUnfound(): int
    {
        return $this->numObjectsUnfound;
    }

    /**
     * Number of objects replicated fewer times than they should be
     * (but found on at least one OSD)
     * @return int
     */
    protected function getNumObjectsDegraded(): int
    {
        return $this->numObjectsDegraded;
    }

    /**
     * Number of objects read
     * @return int
     */
    protected function getNumRd(): int
    {
        return $this->numRd;
    }

    /**
     * Objects read in KB
     * @return int
     */
    protected function getNumRdKb(): int
    {
        return $this->numRdKb;
    }

    /**
     * Number of objects written
     * @return int
     */
    protected function getNumWr(): int
    {
        return $this->numWr;
    }

    /**
     * Objects written in KB
     * @return int
     */
    protected function getNumWrKb(): int
    {
        return $this->numWrKb;
    }

    /**
     * Bytes originally provided by user
     * @return int
     */
    protected function getNumUserBytes(): int
    {
        return $this->numUserBytes;
    }

    /**
     * Bytes passed compression
     * @return int
     */
    protected function getCompressedBytesOrig(): int
    {
        return $this->compressedBytesOrig;
    }

    /**
     * Bytes resulted after compression
     * @return int
     */
    protected function getCompressedBytes(): int
    {
        return $this->compressedBytes;
    }

    /**
     * Bytes allocated at storage
     * @return int
     */
    protected function getCompressedBytesAlloc(): int
    {
        return $this->compressedBytesAlloc;
    }
}
