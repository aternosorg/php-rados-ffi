<?php

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Cluster\ClusterConfig;
use Aternos\Rados\Cluster\Pool\ObjectIterator\ObjectIterator;
use Aternos\Rados\Completion\CompareOperationCompletion;
use Aternos\Rados\Completion\FlushOperationCompletion;
use Aternos\Rados\Completion\ReadOperationCompletion;
use Aternos\Rados\Completion\RemoveOperationCompletion;
use Aternos\Rados\Completion\StatOperationCompletion;
use Aternos\Rados\Completion\WriteOperationCompletion;
use Aternos\Rados\Exception\IOContextException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use Aternos\Rados\Util\ChecksumType;
use Aternos\Rados\Util\Constants;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;
use InvalidArgumentException;

class IOContext extends WrappedType
{
    protected Pool $pool;

    /**
     * @param Pool $pool
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(Pool $pool, CData $data, FFI $ffi)
    {
        parent::__construct($data, $ffi);
        $this->pool = $pool;
        $this->pool->registerChildObject($this);
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
     * Binding for rados_read
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

    /**
     * Binding for rados_checksum
     * Compute checksum from object data
     *
     * The io context determines the snapshot to checksum, if any was set
     * by rados_ioctx_snap_set_read().
     *
     * @param string $objectId - the name of the object to checksum
     * @param ChecksumType $type - the checksum algorithm to utilize
     * @param int $initValue - the init value for the algorithm
     * @param int $length - the number of bytes to checksum
     * @param int $offset - the offset to start checksumming in the object
     * @param int|null $chunkSize - length-aligned chunk size for checksums
     * @return string[] - array of checksums, one for each chunk
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function checksum(string $objectId, ChecksumType $type, int $initValue, int $length, int $offset, ?int $chunkSize = null): array
    {
        $checksumLength = $type->getLength();
        $initString = pack($type->getPackFormat(), $initValue);

        if ($chunkSize === null) {
            $chunkSize = $length;
        }

        if ($chunkSize <= 0) {
            throw new InvalidArgumentException("Chunk size must be greater than 0");
        }

        $resultCount = ceil($length / $chunkSize);
        $resultLength = $resultCount * $checksumLength + 4;

        $checksumBuffer = Buffer::create($this->ffi, $resultLength);
        IOContextException::handle($this->ffi->rados_checksum(
            $this->getCData(), $objectId,
            $type->getCValue($this->ffi),
            $initString, $checksumLength,
            $length, $offset, $chunkSize,
            $checksumBuffer->getCData(), $resultLength
        ));

        $resString = $checksumBuffer->toString();
        $actualResultCount = unpack("V", $resString)[1];
        return array_values(unpack("V" . $actualResultCount, substr($resString, 4)));
    }

    /**
     * Binding for rados_remove
     * Delete an object
     *
     * @note This does not delete any snapshots of the object.
     *
     * @param string $objectId
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function remove(string $objectId): static
    {
        IOContextException::handle($this->ffi->rados_remove($this->getCData(), $objectId));
        return $this;
    }

    /**
     * Binding for rados_trunc
     * Resize an object
     *
     * If this enlarges the object, the new area is logically filled with
     * zeroes. If this shrinks the object, the excess data is removed.
     *
     * @param string $objectId - the name of the object
     * @param int $size - the new size of the object in bytes
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function truncate(string $objectId, int $size): static
    {
        IOContextException::handle($this->ffi->rados_trunc($this->getCData(), $objectId, $size));
        return $this;
    }

    /**
     * Binding for rados_cmpext
     * Compare an on-disk object range with a buffer
     *
     * @param string $objectId - name of the object
     * @param string $compare - buffer containing bytes to be compared with object contents
     * @param int $offset - object byte offset at which to start the comparison
     * @return true|int - true on match, offset of mismatch on failure
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function compareExt(string $objectId, string $compare, int $offset): true|int
    {
        $result = IOContextException::handle($this->ffi->rados_cmpext($this->getCData(), $objectId, $compare, strlen($compare), $offset));
        if ($result === 0) {
            return true;
        }
        return -$result - Constants::MAX_ERRNO;
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
     * Binding for rados_aio_read
     * Asynchronously read data from an object
     *
     * The io context determines the snapshot to read from, if any was set
     * by rados_ioctx_snap_set_read().
     *
     * @param string $objectId
     * @param int $length
     * @param int $offset
     * @param Buffer|null $readBuffer
     * @return ReadOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function readAsync(string $objectId, int $length, int $offset, ?Buffer $readBuffer = null): ReadOperationCompletion
    {
        if ($readBuffer !== null && $readBuffer->getSize() >= $length) {
            $buffer = $readBuffer;
        } else {
            $buffer = Buffer::create($this->ffi, $length);
        }

        $completion = new ReadOperationCompletion($buffer, $this);
        IOContextException::handle($this->ffi->rados_aio_read($this->getCData(), $objectId, $completion->getCData(), $buffer->getCData(), $length, $offset));
        return $completion;
    }

    /**
     * Binding for rados_aio_write
     * Write data to an object asynchronously
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to write
     * @param int $offset - offset to start writing at
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeAsync(string $objectId, string $buffer, int $offset): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_write($this->getCData(), $objectId, $completion->getCData(), $buffer, strlen($buffer), $offset));
        return $completion;
    }

    /**
     * Binding for rados_aio_append
     * Asynchronously append data to an object
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to append
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function appendAsync(string $objectId, string $buffer): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_append($this->getCData(), $objectId, $completion->getCData(), $buffer, strlen($buffer)));
        return $completion;
    }

    /**
     * Binding for rados_aio_write_full
     * Asynchronously write an entire object
     *
     * The object is filled with the provided data. If the object exists,
     * it is atomically truncated and then written.
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to write
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeFullAsync(string $objectId, string $buffer): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_write_full(
            $this->getCData(), $objectId,
            $completion->getCData(),
            $buffer, strlen($buffer)
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_writesame
     * Asynchronously write the same buffer multiple times.
     * $writeLength bytes are written in total, which must be
     * a multiple of the buffer size.
     *
     * @param string $objectId - name of the object
     * @param string $buffer - data to write
     * @param int $writeLength - the total number of bytes to write
     * @param int $offset - byte offset in the object to begin writing at
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeSameAsync(string $objectId, string $buffer, int $writeLength, int $offset): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_writesame(
            $this->getCData(), $objectId,
            $completion->getCData(),
            $buffer, strlen($buffer),
            $writeLength, $offset
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_remove
     * Asynchronously remove an object
     *
     * @param string $objectId - name of the object
     * @return RemoveOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function removeAsync(string $objectId): RemoveOperationCompletion
    {
        $completion = new RemoveOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_remove($this->getCData(), $objectId, $completion->getCData()));
        return $completion;
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
     * Binding for rados_aio_stat
     * Asynchronously get object stats (size/mtime)
     *
     * @param string $objectId - name of the object
     * @return StatOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function statAsync(string $objectId): StatOperationCompletion
    {
        $size = $this->ffi->new("uint64_t");
        $mtime = $this->ffi->new("time_t");
        $completion = new StatOperationCompletion($size, $mtime, $this);
        IOContextException::handle($this->ffi->rados_aio_stat($this->getCData(), $objectId, $completion->getCData(), FFI::addr($size), FFI::addr($mtime)));
        return $completion;
    }

    /**
     * Binding for rados_aio_cmpext
     * Asynchronously compare an on-disk object range with a buffer
     *
     * @param string $objectId - name of the object
     * @param string $compare - buffer containing bytes to be compared with object contents
     * @param int $offset - object byte offset at which to start the comparison
     * @return CompareOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function compareExtAsync(string $objectId, string $compare, int $offset): CompareOperationCompletion
    {
        $completion = new CompareOperationCompletion($this);
        IOContextException::handle($this->ffi->rados_aio_cmpext($this->getCData(), $objectId, $completion->getCData(), $compare, strlen($compare), $offset));
        return $completion;
    }
}
