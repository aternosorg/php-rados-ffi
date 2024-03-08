<?php

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Cluster\ClusterConfig;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectIterator;
use Aternos\Rados\Exception\IOContextException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;

class IOContext extends WrappedType
{
    protected Pool $pool;
    protected bool $closed = false;

    /**
     * @param Pool $pool
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(Pool $pool, CData $data, FFI $ffi)
    {
        $this->pool = $pool;
        parent::__construct($data, $ffi);
    }

    public function __destruct()
    {
        if (!$this->closed) {
            $this->destroy();
        }
    }

    /**
     * @return Pool
     */
    public function getPool(): Pool
    {
        return $this->pool;
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
     */
    public function destroy(): static
    {
        $this->ffi->rados_ioctx_destroy($this->getCData());
        $this->closed = true;
        return $this;
    }

    /**
     * Binding for rados_ioctx_cct
     * Get configuration handle for a pool handle
     *
     * @return ClusterConfig - rados_config_t for this cluster
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getConfigHandle(): ClusterConfig
    {
        return new ClusterConfig($this->ffi->rados_ioctx_cct($this->getCData()), $this->ffi);
    }

    /**
     * Get the cluster handle used by this io context
     *
     * @return Cluster
     */
    public function getCluster(): Cluster
    {
        return $this->getPool()->getCluster();
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
        return new PoolStat($stat, $this->ffi);
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
     */
    public function getLastVersion(): int
    {
        return $this->ffi->rados_get_last_version($this->getCData());
    }

    /**
     * Binding for rados_write
     * Write data from $buffer into the $objectId object, starting at offset $offset.
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to write
     * @param int $offset - offset to start writing at
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function write(string $objectId, string $buffer, int $offset): static
    {
        IOContextException::handle($this->ffi->rados_write($this->getCData(), $objectId, $buffer, strlen($buffer), $offset));
        return $this;
    }

    /**
     * Binding for rados_write_full
     * Write data from $buffer into the $objectId object.
     *
     * The object is filled with the provided data. If the object exists,
     * it is atomically truncated and then written.
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to write
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeFull(string $objectId, string $buffer): static
    {
        IOContextException::handle($this->ffi->rados_write_full($this->getCData(), $objectId, $buffer, strlen($buffer)));
        return $this;
    }

    /**
     * Binding for rados_writesame
     * Write the same bytes from $buffer multiple times into the
     * $objectId object. $writeLength bytes are written in total, which must be
     * a multiple of the buffer size.
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to write
     * @param int $writeLength - the total number of bytes to write
     * @param int $offset - byte offset in the object to begin writing at
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeSame(string $objectId, string $buffer, int $writeLength, int $offset): static
    {
        IOContextException::handle($this->ffi->rados_writesame($this->getCData(), $objectId, $buffer, strlen($buffer), $writeLength, $offset));
        return $this;
    }

    /**
     * Binding for rados_append
     * Append bytes from $buffer into the $objectId object.
     *
     * @param string $objectId - the name of the object
     * @param string $buffer - the data to append
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function append(string $objectId, string $buffer): static
    {
        IOContextException::handle($this->ffi->rados_append($this->getCData(), $objectId, $buffer, strlen($buffer)));
        return $this;
    }

    /**
     * Read data from an object
     *
     * The io context determines the snapshot to read from, if any was set
     * by rados_ioctx_snap_set_read().
     *
     * @param string $objectId - the name of the object to read from
     * @param int $length - the number of bytes to read
     * @param int $offset - the offset to start reading from in the object
     * @param Buffer|null $readBuffer - Optional: temporary buffer to read into.
     *  Reusing a buffer for multiple reads can reduce memory usage and improve performance.
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function read(string $objectId, int $length, int $offset, ?Buffer $readBuffer = null): string
    {
        if ($readBuffer !== null && $readBuffer->getSize() >= $length) {
            $buffer = $readBuffer;
        } else {
            $buffer = Buffer::create($this->ffi, $length);
        }

        $readLength = IOContextException::handle($this->ffi->rados_read($this->getCData(), $objectId, $buffer->getCData(), $length, $offset));
        return $buffer->readString($readLength);
    }
}
