<?php

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Cluster\ClusterConfig;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectCursor;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectIterator;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectRange;
use Aternos\Rados\Cluster\Pool\Snapshot\SelfManagedSnapshot;
use Aternos\Rados\Cluster\Pool\Snapshot\Snapshot;
use Aternos\Rados\Cluster\Pool\Snapshot\SnapshotInterface;
use Aternos\Rados\Completion\FlushOperationCompletion;
use Aternos\Rados\Completion\SelfManagedSnapshotCreateOperationCompletion;
use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Exception\IOContextException;
use Aternos\Rados\Exception\ObjectIteratorException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;

class IOContext extends WrappedType
{
    /**
     * @param Cluster $cluster
     * @param CData $data
     * @param FFI $ffi
     * @internal IOContext objects can be obtained from the Pool object and should not be created directly
     */
    public function __construct(protected Cluster $cluster, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
        $this->cluster->registerChildObject($this);
    }

    /**
     * Binding for rados_ioctx_destroy
     * The opposite of rados_ioctx_create
     *
     * This just tells librados that you no longer need to use the io context.
     * It may not be freed immediately if there are pending asynchronous
     * requests on it, but you should not use an io context again after
     * calling this function on it.
     *
     * @warning This does not guarantee any asynchronous
     * writes have completed. You must call rados_aio_flush()
     * on the io context before destroying it to do that.
     *
     * @warning If this ioctx is used by rados_watch, the caller needs to
     * be sure that all registered watches are disconnected via
     * rados_unwatch() and that rados_watch_flush() is called.  This
     * ensures that a racing watch callback does not make use of a
     * destroyed ioctx.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     * @internal This method is called by the release method and should not be called manually
     */
    protected function destroy(): static
    {
        $this->ffi->rados_ioctx_destroy($this->getCDataUnsafe());
        return $this;
    }

    /**
     * Binding for rados_ioctx_cct
     * Get configuration handle for a pool handle
     *
     * @return ClusterConfig - rados_config_t for this cluster
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function getConfigHandle(): ClusterConfig
    {
        return new ClusterConfig($this->getCluster(), $this->ffi->rados_ioctx_cct($this->getCData()), $this->ffi);
    }

    /**
     * Get the cluster handle used by this io context
     *
     * @return Cluster
     */
    public function getCluster(): Cluster
    {
        return $this->cluster;
    }

    /**
     * Binding for rados_ioctx_pool_stat
     * Get pool usage statistics
     *
     * @return PoolStat
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function poolStat(): PoolStat
    {
        $stat = $this->ffi->new("struct rados_pool_stat_t");
        IOContextException::handle($this->ffi->rados_ioctx_pool_stat($this->getCData(), FFI::addr($stat)));
        return PoolStat::fromStatCData($stat);
    }

    /**
     * Binding for rados_ioctx_pool_requires_alignment2
     * Test whether the specified pool requires alignment or not.
     *
     * @return bool - true if alignment is supported, false if not.
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getPoolRequiresAlignment(): bool
    {
        $result = $this->ffi->new("int");
        IOContextException::handle($this->ffi->rados_ioctx_pool_requires_alignment2($this->getCData(), FFI::addr($result)));
        return (bool)$result->cdata;
    }

    /**
     * Binding for rados_ioctx_pool_required_alignment2
     * Get the alignment flavor of a pool
     *
     * @return int
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getPoolRequiredAlignment(): int
    {
        $result = $this->ffi->new("uint64_t");
        IOContextException::handle($this->ffi->rados_ioctx_pool_required_alignment2($this->getCData(), FFI::addr($result)));
        return $result->cdata;
    }

    /**
     * Binding for rados_ioctx_locator_set_key
     * Set the key for mapping objects to pgs within an io context.
     *
     * The key is used instead of the object name to determine which
     * placement groups an object is put in. This affects all subsequent
     * operations of the io context - until a different locator key is
     * set, all objects in this io context will be placed in the same pg.
     *
     * @param string|null $key - the key to use as the object locator, or NULL to discard any previously set key
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function setLocatorKey(?string $key): static
    {
        $this->ffi->rados_ioctx_locator_set_key($this->getCData(), $key);
        return $this;
    }

    /**
     * Binding for rados_ioctx_set_namespace
     * Set the namespace for objects within an io context
     *
     *  The namespace specification further refines a pool into different
     *  domains.  The mapping of objects to pgs is also based on this
     *  value.
     *
     * @param string|null $namespace - the name to use as the namespace, or NULL use the default namespace
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function setNamespace(?string $namespace): static
    {
        $this->ffi->rados_ioctx_set_namespace($this->getCData(), $namespace);
        return $this;
    }

    /**
     * Binding for rados_ioctx_get_namespace
     * Get the namespace for objects within the io context
     *
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getNamespace(): string
    {
        $length = 512;
        do {
            $buffer = Buffer::create($this->ffi, $length);
            $res = $this->ffi->rados_ioctx_get_namespace($this->getCData(), $buffer->getCData(), $length);
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        IOContextException::handle($res);
        return $buffer->readString();
    }

    /**
     * Open an object iterator for this io context
     *
     * @return ObjectIterator
     * @throws RadosException
     */
    public function createObjectIterator(): ObjectIterator
    {
        return ObjectIterator::open($this);
    }

    /**
     * Binding for rados_get_last_version
     * Return the version of the last object read or written to.
     *
     * This exposes the internal version number of the last object read or
     * written via this io context
     *
     * @return int
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function getLastVersion(): int
    {
        return $this->ffi->rados_get_last_version($this->getCData());
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return parent::isValid() && $this->getCluster()->isValid();
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->destroy();
    }

    /**
     * Binding for rados_aio_flush
     * Block until all pending writes in an io context are safe
     *
     * This is not equivalent to calling waitForSafe() on all
     * write completions, since this waits for the associated callbacks to
     * complete as well.
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function flushAsyncWrites(): static
    {
        IOContextException::handle($this->ffi->rados_aio_flush($this->getCData()));
        return $this;
    }

    /**
     * Binding for rados_aio_flush_async
     * Flush all pending writes in an io context asynchronously
     *
     * @return FlushOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function flushAsyncWritesAsync(): FlushOperationCompletion
    {
        $completion = new FlushOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_flush_async($this->getCData(), $completion->getCData()));
        return $completion;
    }

    /**
     * @param string $objectId
     * @return RadosObject
     */
    public function getObject(string $objectId): RadosObject
    {
        return new RadosObject($objectId, $this);
    }

    /**
     * Binding for rados_getxattrs_next
     * Read all extended attributes from an iterator
     *
     * @param CData $iterator
     * @return string[]
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     * @internal This method should not be called directly, use getXAttributes() or getXAttributesAsync() instead
     */
    public function getXAttributesFromIterator(CData $iterator): array
    {
        $name = $this->ffi->new('char*');
        $value = $this->ffi->new('char*');
        $size = $this->ffi->new('size_t');
        $result = [];
        do {
            RadosObjectException::handle($this->ffi->rados_getxattrs_next(
                $iterator,
                FFI::addr($name),
                FFI::addr($value), FFI::addr($size)
            ));

            if (!FFI::isNull($name) && !FFI::isNull($value)) {
                $result[FFI::string($name)] = FFI::string($value, $size->cdata);
            }
        } while (!FFI::isNull($name) && !FFI::isNull($value));

        return $result;
    }

    /**
     * Get cursor handle pointing to the *beginning* of a pool.
     *
     * @return ObjectCursor
     * @throws RadosException
     * @throws ObjectIteratorException
     */
    public function getCursorAtBeginning(): ObjectCursor
    {
        return ObjectCursor::getAtBeginning($this);
    }

    /**
     * Get cursor handle pointing to the *end* of a pool.
     *
     * @return ObjectCursor
     * @throws RadosException
     * @throws ObjectIteratorException
     */
    public function getCursorAtEnd(): ObjectCursor
    {
        return ObjectCursor::getAtEnd($this);
    }

    /**
     * Get a range between a start cursor (inclusive) and an end cursor (exclusive)
     *
     * @param ObjectCursor $start
     * @param ObjectCursor $end
     * @return ObjectRange
     */
    public function getObjectRange(ObjectCursor $start, ObjectCursor $end): ObjectRange
    {
        return new ObjectRange($this, $start, $end);
    }

    /**
     * Binding for rados_ioctx_selfmanaged_snap_create
     * Allocate an ID for a self-managed snapshot
     *
     * Get a unique ID to put in the snaphot context to create a
     * snapshot. A clone of an object is not created until a write with
     * the new snapshot context is completed.
     *
     * @return SelfManagedSnapshot
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createSelfManagedSnapshot(): SelfManagedSnapshot
    {
        $id = $this->ffi->new("rados_snap_t");
        RadosObjectException::handle($this->ffi->rados_ioctx_selfmanaged_snap_create($this->getCData(), FFI::addr($id)));
        return new SelfManagedSnapshot($this, $id->cdata);
    }

    /**
     * Binding for rados_aio_ioctx_selfmanaged_snap_create
     * Allocate an ID for a self-managed snapshot
     *
     * Get a unique ID to put in the snaphot context to create a
     * snapshot. A clone of an object is not created until a write with
     * the new snapshot context is completed.
     *
     * @return SelfManagedSnapshotCreateOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createSelfManagedSnapshotAsync(): SelfManagedSnapshotCreateOperationCompletion
    {
        $id = $this->ffi->new("rados_snap_t");
        $completion = new SelfManagedSnapshotCreateOperationCompletion($id, $this);
        RadosObjectException::handle($this->ffi->rados_aio_ioctx_selfmanaged_snap_create($this->getCData(), FFI::addr($id), $completion->getCData()));
        return $completion;
    }

    /**
     * Binding for rados_ioctx_snap_create
     * Create a pool-wide snapshot
     *
     * @param string $name
     * @return Snapshot
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createSnapshot(string $name): Snapshot
    {
        IOContextException::handle($this->ffi->rados_ioctx_snap_create($this->getCData(), $name));
        return new Snapshot($this, null, $name);
    }

    /**
     * Get a snapshot by name
     *
     * @note This method does not check if the snapshot exists
     *
     * @param string $name
     * @return Snapshot
     */
    public function getSnapshotByName(string $name): Snapshot
    {
        return new Snapshot($this, null, $name);
    }

    /**
     * Get a snapshot by id
     *
     * @note This method does not check if the snapshot exists
     *
     * @param int $id
     * @return Snapshot
     */
    public function getSnapshotById(int $id): Snapshot
    {
        return new Snapshot($this, $id, null);
    }

    /**
     * @param SnapshotInterface|null $snapshot - the snapshot to set as the read snapshot,
     * or null for no snapshot (i.e. normal operation)
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setReadSnapshot(?SnapshotInterface $snapshot): static
    {
        $snapId = $snapshot?->getId();
        if ($snapId === null) {
            $snapId = Constants::SNAP_HEAD;
        }
        IOContextException::handle($this->ffi->rados_ioctx_snap_set_read($this->getCData(), $snapId));
        return $this;
    }

    /**
     * Binding for rados_ioctx_selfmanaged_snap_set_write_ctx
     * Set the snapshot context for use when writing to objects
     *
     * This is stored in the io context, and applies to all future writes.
     *
     * @note I do not understand how any of this works, which is why this is just a plain binding to the C function
     *
     * @param int $seq
     * @param array $snaps
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setSelfManagedSnapshotWriteContext(int $seq, array $snaps): static
    {
        $snapsData = $this->ffi->new($this->ffi->arrayType($this->ffi->type("rados_snap_t"), [count($snaps)]));
        foreach (array_values($snaps) as $i => $snap) {
            $snapsData[$i] = $snap->getId();
        }
        IOContextException::handle($this->ffi->rados_ioctx_selfmanaged_snap_set_write_ctx($this->getCData(), $seq, $snapsData, count($snaps)));
        return $this;
    }

    /**
     * Binding for rados_ioctx_snap_list
     * List all the ids of pool snapshots
     *
     * @return Snapshot[]
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function listSnapshots(): array
    {
        $length = 32;
        do {
            $buffer = $this->ffi->new($this->ffi->arrayType($this->ffi->type("rados_snap_t"), [$length]));
            $res = $this->ffi->rados_ioctx_snap_list($this->getCData(), $buffer, $length);
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        $resultLength = IOContextException::handle($res);

        $snapshots = [];
        for ($i = 0; $i < $resultLength; $i++) {
            $snapshots[] = new Snapshot($this, $buffer[$i], null);
        }
        return $snapshots;
    }
}
