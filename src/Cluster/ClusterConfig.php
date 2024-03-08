<?php

namespace Aternos\Rados\Cluster;

use Aternos\Rados\Util\WrappedType;

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

}
