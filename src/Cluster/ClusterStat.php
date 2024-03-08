<?php
/** @noinspection PhpUndefinedFieldInspection */

namespace Aternos\Rados\Cluster;

use Aternos\Rados\Util\WrappedType;

class ClusterStat extends WrappedType
{
    /**
     * Number of objects
     * @return int
     */
    public function getNumObjects(): int
    {
        return $this->getCData()->numObjects;
    }

    /**
     * Total device size
     * @return int
     */
    public function getKb(): int
    {
        return $this->getCData()->kb;
    }

    /**
     * Total used
     * @return int
     */
    public function getKbUsed(): int
    {
        return $this->getCData()->kb_used;
    }

    /**
     * Total available/free
     * @return int
     */
    public function getKbAvail(): int
    {
        return $this->getCData()->kb_avail;
    }
}
