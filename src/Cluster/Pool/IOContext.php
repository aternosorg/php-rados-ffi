<?php

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Cluster\ClusterConfig;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectCursor;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectIterator;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectRange;
use Aternos\Rados\Completion\FlushOperationCompletion;
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
        $step = 512;
        $length = $step;
        do {
            $buffer = Buffer::create($this->ffi, $length);
            $res = $this->ffi->rados_ioctx_get_namespace($this->getCData(), $buffer->getCData(), $length);
            $length += $step;
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
}
