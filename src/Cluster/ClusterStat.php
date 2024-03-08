<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Aternos\Rados\Cluster;

use Aternos\Rados\WrappedType;

class ClusterStat extends WrappedType
{
    /**
     * Number of objects
     * @return int
     */
    public function getNumObjects(): int
    {
        return $this->getData()->numObjects;
    }

    /**
     * Total device size
     * @return int
     */
    public function getKb(): int
    {
        return $this->getData()->kb;
    }

    /**
     * Total used
     * @return int
     */
    public function getKbUsed(): int
    {
        return $this->getData()->kb_used;
    }

    /**
     * Total available/free
     * @return int
     */
    public function getKbAvail(): int
    {
        return $this->getData()->kb_avail;
    }
}
