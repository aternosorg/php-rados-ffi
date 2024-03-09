<?php

namespace Aternos\Rados\Cluster;

use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;

/**
 * A handle for the ceph configuration context for the rados_t cluster
 * instance.  This can be used to share configuration context/state
 * (e.g., logging configuration) between librados instance.
 *
 * @warning The config context does not have independent reference
 * counting.  As such, a rados_config_t handle retrieved from a given
 * rados_t is only valid as long as that rados_t.
 */
class ClusterConfig extends WrappedType
{
    /**
     * @param Cluster $cluster
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(protected Cluster $cluster, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
    }

    /**
     * @return Cluster
     */
    public function getCluster(): Cluster
    {
        return $this->cluster;
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return parent::isValid() && $this->cluster->isValid();
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        // No need to release the config context, it is managed by the cluster
    }
}
