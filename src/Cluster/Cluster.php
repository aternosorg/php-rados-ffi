<?php

namespace Aternos\Rados\Cluster;

use Aternos\Rados\Cluster\Pool\Pool;
use Aternos\Rados\Exception\ClusterException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use Aternos\Rados\Util\WrappedType;
use Exception;
use FFI;

class Cluster extends WrappedType
{
    protected bool $connected = false;

    /**
     * Binding for rados_create
     * Create a handle for communicating with a RADOS cluster.
     *
     *  Ceph environment variables are read when this is called, so if
     *  $CEPH_ARGS specifies everything you need to connect, no further
     *  configuration is necessary.
     *
     * @param FFI $ffi
     * @param string|null $userId - the user to connect as (i.e. admin, not client.admin)
     * @return static
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function create(FFI $ffi, ?string $userId = null): static
    {
        $cluster = $ffi->new("rados_t");
        ClusterException::handle($ffi->rados_create(FFI::addr($cluster), $userId));
        return new static($cluster, $ffi);
    }

    /**
     * Binding for rados_create2
     * Extended version of rados_create.
     *
     *  Like rados_create, but
     *  1) don't assume 'client\.'+id; allow full specification of name
     *  2) allow specification of cluster name
     *  3) flags for future expansion
     *
     * @param FFI $ffi
     * @param string|null $clusterName
     * @param string|null $userId
     * @param int $flags
     * @return static
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function create2(FFI $ffi, ?string $clusterName, ?string $userId, int $flags = 0): static
    {
        $cluster = $ffi->new("rados_t");
        ClusterException::handle($ffi->rados_create2(FFI::addr($cluster), $clusterName, $userId, $flags));
        return new static($cluster, $ffi);
    }

    /**
     * Binding for rados_create_with_context
     * @param FFI $ffi
     * @param ClusterConfig $config
     * @return static
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function createWithContext(FFI $ffi, ClusterConfig $config): static
    {
        $cluster = $ffi->new("rados_t");
        ClusterException::handle($ffi->rados_create_with_context(FFI::addr($cluster), $config->getCData()));
        return new static($cluster, $ffi);
    }

    /**
     * Binding for rados_ping_monitor
     * Ping the monitor with ID mon_id
     *
     *  The result buffer is allocated on the heap; the caller is
     *  expected to release that memory with rados_buffer_free().  The
     *  buffer and length pointers can be NULL, in which case they are
     *  not filled in.
     *
     * @param string $monitorId
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function pingMonitor(string $monitorId): string
    {
        $outStr = $this->ffi->new("char*");
        $outStrLen = $this->ffi->new("size_t");
        RadosException::handle($this->ffi->rados_ping_monitor($this->getCData(), $monitorId, FFI::addr($outStr), FFI::addr($outStrLen)));
        $result = FFI::string($outStr, $outStrLen->cdata);
        $this->ffi->rados_buffer_free($outStr);
        return $result;
    }

    /**
     * Binding for rados_conf_read_file
     * Configure the cluster handle using a Ceph config file
     *
     *  If path is NULL, the default locations are searched, and the first
     *  found is used. The locations are:
     *  - $CEPH_CONF (environment variable)
     *  - /etc/ceph/ceph.conf
     *  - ~/.ceph/config
     *  - ceph.conf (in the current working directory)
     *
     * @pre rados_connect() has not been called on the cluster handle
     *
     * @param string|null $path - path to a Ceph configuration file
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function configReadFile(?string $path): static
    {
        ClusterException::handle($this->ffi->rados_conf_read_file($this->getCData(), $path));
        return $this;
    }

    /**
     * Binding for rados_connect
     * Connect to the cluster.
     *
     * @note BUG: Before calling this, calling a function that communicates with the
     *  cluster will crash.
     *
     * @pre The cluster handle is configured with at least a monitor
     *  address. If cephx is enabled, a client name and secret must also be
     *  set.
     *
     * @post If this succeeds, any function in librados may be used
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function connect(): static
    {
        ClusterException::handle($this->ffi->rados_connect($this->getCData()));
        $this->connected = true;
        return $this;
    }

    /**
     * Binding for rados_shutdown
     * Disconnects from the cluster.
     *
     *  For clean up, this is only necessary after rados_connect() has
     *  succeeded.
     *
     * @warning This does not guarantee any asynchronous writes have
     *  completed. To do that, you must call rados_aio_flush() on all open
     *  io contexts.
     *
     * @warning We implicitly call rados_watch_flush() on shutdown.  If
     *  there are watches being used, this should be done explicitly before
     *  destroying the relevant IoCtx.  We do it here as a safety measure.
     *
     * @post the cluster handle cannot be used again
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     * @internal This method is called by the release method and should not be called manually
     */
    protected function shutdown(): static
    {
        $this->ffi->rados_shutdown($this->getCData());
        $this->connected = false;
        return $this;
    }

    /**
     * Binding for rados_conf_parse_argv
     * Configure the cluster handle with command line arguments
     *
     *  argv can contain any common Ceph command line option, including any
     *  configuration parameter prefixed by '--' and replacing spaces with
     *  dashes or underscores. For example, the following options are equivalent:
     *  - --mon-host 10.0.0.1:6789
     *  - --mon_host 10.0.0.1:6789
     *  - -m 10.0.0.1:6789
     *
     * @pre rados_connect() has not been called on the cluster handle
     *
     * @param array $args
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function configParseArgv(array $args): static
    {
        $argv = static::createStringArray($this->ffi, $args);
        try {
            ClusterException::handle($this->ffi->rados_conf_parse_argv($this->getCData(), count($args), $argv));
        } catch (Exception $e) {
            static::freeStringArray($argv);
            throw $e;
        }

        static::freeStringArray($argv);
        return $this;
    }

    /**
     * Binding for rados_conf_parse_argv_remainder
     * Configure the cluster handle with command line arguments, returning
     *  any remainders.  Same rados_conf_parse_argv, except for extra
     *  remargv argument to hold returns unrecognized arguments.
     *
     * @pre rados_connect() has not been called on the cluster handle
     *
     * @param array $args
     * @return string[]
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function configParseArgvRemainder(array $args): array
    {
        $argv = static::createStringArray($this->ffi, $args);
        $remainder = $this->ffi->new(FFI::arrayType($this->ffi->type("char*"), [count($args)]));

        try {
            ClusterException::handle($this->ffi->rados_conf_parse_argv_remainder($this->getCData(), count($args), $argv, $remainder));
        } catch (Exception $e) {
            static::freeStringArray($argv);
            throw $e;
        }

        $result = [];
        foreach ($remainder as $value) {
            if ($value !== null) {
                $result[] = FFI::string($value);
            }
        }

        static::freeStringArray($argv);
        return $result;
    }

    /**
     * Binding for rados_conf_parse_env
     * Configure the cluster handle based on an environment variable
     *
     *  The contents of the environment variable are parsed as if they were
     *  Ceph command line options. If var is NULL, the CEPH_ARGS
     *  environment variable is used.
     *
     * @pre rados_connect() has not been called on the cluster handle
     *
     * @note BUG: this is not threadsafe - it uses a static buffer
     *
     * @param string $envVar
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function configParseEnv(string $envVar): static
    {
        ClusterException::handle($this->ffi->rados_conf_parse_env($this->getCData(), $envVar));
        return $this;
    }

    /**
     * Binding for rados_conf_set
     * Set a configuration option
     *
     * @pre rados_connect() has not been called on the cluster handle
     *
     * @param string $option
     * @param string $value
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function configSet(string $option, string $value): static
    {
        ClusterException::handle($this->ffi->rados_conf_set($this->getCData(), $option, $value));
        return $this;
    }

    /**
     * Binding for rados_conf_get
     * Get the value of a configuration option
     *
     * @param string $option
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function configGet(string $option): string
    {
        $step = 512;
        $length = $step;
        do {
            $buffer = Buffer::create($this->ffi, $length);
            $res = $this->ffi->rados_conf_get($this->getCData(), $option, $buffer->getCData(), $length);
            $length += $step;
        } while ($res < 0 && -$res === Errno::ENAMETOOLONG->value);
        ClusterException::handle($res);
        return $buffer->readString();
    }

    /**
     * Binding for rados_cluster_stat
     * Read usage info about the cluster
     *
     *  This tells you total space, space used, space available, and number
     *  of objects. These are not updated immediately when data is written,
     *  they are eventually consistent.
     *
     * @return ClusterStat
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function clusterStat(): ClusterStat
    {
        $stat = $this->ffi->new("struct rados_cluster_stat_t");
        ClusterException::handle($this->ffi->rados_cluster_stat($this->getCData(), FFI::addr($stat)));
        return ClusterStat::fromStatCData($stat);
    }

    /**
     * Binding for rados_cluster_fsid
     * Get the fsid of the cluster as a hexadecimal string.
     *
     *  The fsid is a unique id of an entire Ceph cluster.
     *
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getFsid(): string
    {
        $step = 512;
        $length = 37;
        do {
            $buffer = Buffer::create($this->ffi, $length);
            $res = $this->ffi->rados_cluster_fsid($this->getCData(), $buffer->getCData(), $length);
            $length += $step;
        } while (-$res === Errno::ERANGE->value);
        ClusterException::handle($res);
        return $buffer->readString();
    }

    /**
     * Binding for rados_wait_for_latest_osdmap
     * Get/wait for the most recent osdmap
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function waitForLatestOsdMap(): static
    {
        ClusterException::handle($this->ffi->rados_wait_for_latest_osdmap($this->getCData()));
        return $this;
    }

    /**
     * Binding for rados_pool_list
     * List pools
     *
     *  Gets a list of pool names as strings.
     *
     * @return string[]
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function listPools(): array
    {
        $length = ClusterException::handle($this->ffi->rados_pool_list($this->getCData(), null, 0));
        $buffer = Buffer::create($this->ffi, $length);
        ClusterException::handle($this->ffi->rados_pool_list($this->getCData(), $buffer->getCData(), $length));

        return static::parseNullTerminatedStringList($buffer, $length);
    }

    /**
     * Get all pools as Pool instances
     *
     * @return Pool[]
     * @throws RadosException
     */
    public function getPools(): array
    {
        $pools = [];
        foreach ($this->listPools() as $poolName) {
            $pools[] = $this->getPool($poolName);
        }
        return $pools;
    }

    /**
     * Binding for rados_inconsistent_pg_list
     * List inconsistent placement groups of the given pool
     *
     * @param int $poolId
     * @return string[]
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function listInconsistentPG(int $poolId): array
    {
        $length = ClusterException::handle($this->ffi->rados_inconsistent_pg_list($this->getCData(), $poolId, null, 0));
        $buffer = Buffer::create($this->ffi, $length);
        ClusterException::handle($this->ffi->rados_inconsistent_pg_list($this->getCData(), $poolId, $buffer->getCData(), $length));

        return static::parseNullTerminatedStringList($buffer, $length);
    }

    /**
     * Binding for rados_cct
     * Get a configuration handle for a rados cluster handle
     *
     * This handle is valid only as long as the cluster handle is valid.
     *
     * @return ClusterConfig
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function getConfigHandle(): ClusterConfig
    {
        return new ClusterConfig($this, $this->ffi->rados_cct($this->getCData()), $this->ffi);
    }

    /**
     * Binding for rados_get_instance_id
     * Get a global id for current instance
     *
     *  This id is a unique representation of current connection to the cluster
     *
     * @return int
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function getInstanceId(): int
    {
        return $this->ffi->rados_get_instance_id($this->getCData());
    }

    /**
     * Binding for rados_get_min_compatible_osd
     * Gets the minimum compatible OSD version
     *
     * @return int - minimum compatible OSD version based upon the current features
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getMinCompatibleOsd(): int
    {
        $result = $this->ffi->new("int8_t");
        ClusterException::handle($this->ffi->rados_get_min_compatible_osd($this->getCData(), FFI::addr($result)));
        return $result->cdata;
    }

    /**
     * Binding for rados_get_min_compatible_client
     * Gets the minimum compatible client version
     *
     * @return ClientVersionRequirement
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getMinCompatibleClient(): ClientVersionRequirement
    {
        $minVersion = $this->ffi->new("int8_t");
        $requiredMinVersion = $this->ffi->new("int8_t");
        ClusterException::handle($this->ffi->rados_get_min_compatible_client($this->getCData(), FFI::addr($minVersion), FFI::addr($requiredMinVersion)));
        return new ClientVersionRequirement($minVersion->cdata, $requiredMinVersion->cdata);
    }

    /**
     * Binding for rados_pool_create
     * Create a pool with default settings
     * The default crush rule is rule 0.
     *
     * @param string $name
     * @return Pool
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createPool(string $name): Pool
    {
        ClusterException::handle($this->ffi->rados_pool_create($this->getCData(), $name));
        return new Pool($this, $name, null, $this->getCData(), $this->ffi);
    }

    /**
     * Binding for rados_pool_create_with_crush_rule
     * Create a pool with a specific CRUSH rule
     *
     * @param string $name - the name of the new pool
     * @param int $crushRuleNumber - which rule to use for placement in the new pool1
     * @return Pool
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createPoolWithCrushRule(string $name, int $crushRuleNumber): Pool
    {
        ClusterException::handle($this->ffi->rados_pool_create_with_crush_rule($this->getCData(), $name, $crushRuleNumber));
        return new Pool($this, $name, null);
    }

    /**
     * Get a Pool instance by name
     *
     * @param string $name
     * @return Pool
     */
    public function getPool(string $name): Pool
    {
        return new Pool($this, $name, null);
    }

    /**
     * Get a Pool instance by id
     *
     * @param int $id
     * @return Pool
     */
    public function getPoolById(int $id): Pool
    {
        return new Pool($this, null, $id);
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->shutdown();
    }
}
