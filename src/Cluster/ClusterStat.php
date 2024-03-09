<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Aternos\Rados\Cluster;

use FFI\CData;

class ClusterStat
{
    /**
     * @param CData $data - CData of type rados_cluster_stat_t
     * @return static
     */
    public static function fromStatCData(CData $data): static
    {
        return new static(
            $data->num_objects,
            $data->kb,
            $data->kb_used,
            $data->kb_avail
        );
    }

    /**
     * @param int $numObjects
     * @param int $kb
     * @param int $kbUsed
     * @param int $kbAvail
     */
    public function __construct(
        protected int $numObjects,
        protected int $kb,
        protected int $kbUsed,
        protected int $kbAvail
    )
    {
    }

    /**
     * Number of objects
     * @return int
     */
    public function getNumObjects(): int
    {
        return $this->numObjects;
    }

    /**
     * Total device size
     * @return int
     */
    public function getKb(): int
    {
        return $this->kb;
    }

    /**
     * Total used
     * @return int
     */
    public function getKbUsed(): int
    {
        return $this->kbUsed;
    }

    /**
     * Total available/free
     * @return int
     */
    public function getKbAvail(): int
    {
        return $this->kbAvail;
    }
}
