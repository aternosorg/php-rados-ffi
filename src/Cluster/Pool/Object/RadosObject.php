<?php

namespace Aternos\Rados\Cluster\Pool\Object;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\Lock\ForeignLock;
use Aternos\Rados\Cluster\Pool\Object\Lock\Lock;
use Aternos\Rados\Cluster\Pool\Snapshot\Snapshot;
use Aternos\Rados\Completion\CompareOperationCompletion;
use Aternos\Rados\Completion\OsdClassMethodExecuteOperationCompletion;
use Aternos\Rados\Completion\ReadOperationCompletion;
use Aternos\Rados\Completion\RemoveOperationCompletion;
use Aternos\Rados\Completion\StatOperationCompletion;
use Aternos\Rados\Completion\WriteOperationCompletion;
use Aternos\Rados\Completion\XAttributes\GetXAttributeOperationCompletion;
use Aternos\Rados\Completion\XAttributes\GetXAttributesOperationCompletion;
use Aternos\Rados\Completion\XAttributes\RemoveXAttributeCompletion;
use Aternos\Rados\Completion\XAttributes\SetXAttributeCompletion;
use Aternos\Rados\Constants\AllocHintFlag;
use Aternos\Rados\Constants\ChecksumType;
use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Constants\LockFlag;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use Aternos\Rados\Util\TimeValue;
use FFI;
use InvalidArgumentException;
use Random\RandomException;

class RadosObject
{
    /**
     * @param string $id
     * @param IOContext $ioContext
     */
    public function __construct(protected string $id, protected IOContext $ioContext)
    {
    }

    /**
     * @return IOContext
     */
    public function getIOContext(): IOContext
    {
        return $this->ioContext;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Binding for rados_write
     * Write data from $buffer into this object, starting at offset $offset.
     *
     * @param string $buffer - data to write
     * @param int $offset - offset to start writing at
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function write(string $buffer, int $offset): static
    {
        RadosObjectException::handle(
            $this->getIOContext()->getFFI()->rados_write($this->getIOContext()->getCData(),
                $this->getId(),
                $buffer, strlen($buffer), $offset
            ));
        return $this;
    }

    /**
     * Binding for rados_write_full
     * Write data from $buffer into this object.
     *
     * The object is filled with the provided data. If the object exists,
     * it is atomically truncated and then written.
     *
     * @param string $buffer - data to write
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeFull(string $buffer): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_write_full(
            $this->getIOContext()->getCData(),
            $this->getId(), $buffer, strlen($buffer)
        ));
        return $this;
    }

    /**
     * Binding for rados_writesame
     * Write the same bytes from $buffer multiple times into this
     * object. $writeLength bytes are written in total, which must be
     * a multiple of the buffer size.
     *
     * @param string $buffer - data to write
     * @param int $writeLength - the total number of bytes to write
     * @param int $offset - byte offset in the object to begin writing at
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeSame(string $buffer, int $writeLength, int $offset): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_writesame(
            $this->getIOContext()->getCData(), $this->getId(),
            $buffer, strlen($buffer),
            $writeLength, $offset
        ));
        return $this;
    }

    /**
     * Binding for rados_append
     * Append bytes from $buffer to this object.
     *
     * @param string $buffer - the data to append
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function append(string $buffer): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_append(
            $this->getIOContext()->getCData(), $this->getId(),
            $buffer, strlen($buffer)
        ));
        return $this;
    }

    /**
     * Binding for rados_read
     * Read data from this object
     *
     * The io context determines the snapshot to read from, if any was set
     * by rados_ioctx_snap_set_read().
     *
     * @param int $length - the number of bytes to read
     * @param int $offset - the offset to start reading from in the object
     * @param Buffer|null $readBuffer - Optional: temporary buffer to read into.
     *  Reusing a buffer for multiple reads can reduce memory usage and improve performance.
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function read(int $length, int $offset, ?Buffer $readBuffer = null): string
    {
        if ($readBuffer !== null && $readBuffer->getSize() >= $length) {
            $buffer = $readBuffer;
        } else {
            $buffer = Buffer::create($this->getIOContext()->getFFI(), $length);
        }

        $readLength = RadosObjectException::handle($this->getIOContext()->getFFI()->rados_read(
            $this->getIOContext()->getCData(), $this->getId(),
            $buffer->getCData(), $length, $offset
        ));
        return $buffer->readString($readLength);
    }

    /**
     * Binding for rados_checksum
     * Compute checksum from object data
     *
     * The io context determines the snapshot to checksum, if any was set
     * by rados_ioctx_snap_set_read().
     *
     * @param ChecksumType $type - the checksum algorithm to utilize
     * @param int $initValue - the init value for the algorithm
     * @param int $length - the number of bytes to checksum
     * @param int $offset - the offset to start checksumming in the object
     * @param int|null $chunkSize - length-aligned chunk size for checksums
     * @return string[] - array of checksums, one for each chunk
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function checksum(ChecksumType $type, int $initValue, int $length, int $offset, ?int $chunkSize = null): array
    {
        $checksumLength = $type->getLength();
        $initString = $type->createInitString($initValue);

        if ($chunkSize === null) {
            $chunkSize = $length;
        }

        if ($chunkSize <= 0) {
            throw new InvalidArgumentException("Chunk size must be greater than 0");
        }

        $resultCount = ceil($length / $chunkSize);
        $resultLength = $resultCount * $checksumLength + 4;

        $checksumBuffer = Buffer::create($this->getIOContext()->getFFI(), $resultLength);
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_checksum(
            $this->getIOContext()->getCData(), $this->getId(),
            $type->getCValue($this->getIOContext()->getFFI()),
            $initString, $checksumLength,
            $length, $offset, $chunkSize,
            $checksumBuffer->getCData(), $resultLength
        ));

        return $type->unpack($checksumBuffer->toString());
    }

    /**
     * Binding for rados_remove
     * Delete an object
     *
     * @note This does not delete any snapshots of the object.
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function remove(): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_remove($this->getIOContext()->getCData(), $this->getId()));
        return $this;
    }

    /**
     * Binding for rados_trunc
     * Resize an object
     *
     * If this enlarges the object, the new area is logically filled with
     * zeroes. If this shrinks the object, the excess data is removed.
     *
     * @param int $size - the new size of the object in bytes
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function truncate(int $size): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_trunc($this->getIOContext()->getCData(), $this->getId(), $size));
        return $this;
    }

    /**
     * Binding for rados_cmpext
     * Compare an on-disk object range with a buffer
     *
     * @param string $compare - buffer containing bytes to be compared with object contents
     * @param int $offset - object byte offset at which to start the comparison
     * @return true|int - true on match, offset of mismatch on failure
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function compareExt(string $compare, int $offset): true|int
    {
        $result = RadosObjectException::handle($this->getIOContext()->getFFI()->rados_cmpext(
            $this->getIOContext()->getCData(), $this->getId(),
            $compare, strlen($compare), $offset
        ));

        if ($result === 0) {
            return true;
        }
        return -$result - Constants::MAX_ERRNO;
    }

    /**
     * Binding for rados_getxattr
     * Get the value of an extended attribute on an object.
     *
     * @param string $name - name of the attribute
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getXAttribute(string $name): string
    {
        $length = 512;
        do {
            $buffer = Buffer::create($this->getIOContext()->getFFI(), $length);
            $res = $this->getIOContext()->getFFI()->rados_getxattr(
                $this->getIOContext()->getCData(), $this->getId(),
                $name, $buffer->getCData(), $length
            );
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        RadosObjectException::handle($res);
        return $buffer->readString($res);
    }

    /**
     * Binding for rados_setxattr
     * Set an extended attribute on an object.
     *
     * @param string $name - name of the attribute
     * @param string $value - value of the attribute
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setXAttribute(string $name, string $value): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_setxattr(
            $this->getIOContext()->getCData(), $this->getId(), $name,
            $value, strlen($value)
        ));
        return $this;
    }

    /**
     * Binding for rados_rmxattr
     * Delete an extended attribute from an object.
     *
     * @param string $name
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function removeXAttribute(string $name): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_rmxattr($this->getIOContext()->getCData(), $this->getId(), $name));
        return $this;
    }

    /**
     * Binding for rados_getxattrs, rados_getxattrs_end
     * Get all extended attributes of an object as an associative array
     *
     * @return string[]
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getXAttributes(): array
    {
        $ffi = $this->getIOContext()->getFFI();
        $iterator = $ffi->new('rados_xattrs_iter_t');
        RadosObjectException::handle($ffi->rados_getxattrs(
            $this->getIOContext()->getCData(),
            $this->getId(),
            FFI::addr($iterator)
        ));

        try {
            return $this->ioContext->getXAttributesFromIterator($iterator);
        } catch (RadosException $e) {
            $ffi->rados_getxattrs_end($iterator);
            throw $e;
        }
    }

    /**
     * Binding for rados_exec
     * Execute an OSD class method on an object
     *
     * The OSD has a plugin mechanism for performing complicated
     * operations on an object atomically. These plugins are called
     * classes. This function allows librados users to call the custom
     * methods. The input and output formats are defined by the class.
     * Classes in ceph.git can be found in src/cls subdirectories
     *
     * @param string $class - name of the class
     * @param string $method - name of the method
     * @param string $input - input data for the method
     * @param int $maxOutputSize - maximum size of the output buffer
     * @param Buffer|null $outputBuffer - Optional: temporary buffer to read into.
     * @return OsdClassMethodExecuteResult
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function execute(string $class, string $method, string $input, int $maxOutputSize, ?Buffer $outputBuffer = null): OsdClassMethodExecuteResult
    {
        if ($outputBuffer !== null && $outputBuffer->getSize() >= $maxOutputSize) {
            $buffer = $outputBuffer;
        } else {
            $buffer = Buffer::create($this->getIOContext()->getFFI(), $maxOutputSize);
        }
        $result = $this->getIOContext()->getFFI()->rados_exec(
            $this->getIOContext()->getCData(),
            $this->getId(),
            $class, $method,
            $input, strlen($input),
            $buffer->getCData(), $maxOutputSize
        );

        return new OsdClassMethodExecuteResult($result, $buffer);
    }

    /**
     * Pin an object in the cache tier
     *
     * When an object is pinned in the cache tier, it stays in the cache
     * tier, and won't be flushed out.
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function pinCacheTier(): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_cache_pin(
            $this->getIOContext()->getCData(),
            $this->getId()
        ));
        return $this;
    }

    /**
     * Unpin an object in the cache tier
     *
     * After an object is unpinned in the cache tier, it can be flushed out
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unpinCacheTier(): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_cache_unpin(
            $this->getIOContext()->getCData(),
            $this->getId()
        ));
        return $this;
    }

    /**
     * Binding for rados_set_alloc_hint, rados_set_alloc_hint2
     * Set allocation hint for an object
     *
     * This is an advisory operation, it will always succeed (as if it was
     * submitted with a LIBRADOS_OP_FLAG_FAILOK flag set) and is not
     * guaranteed to do anything on the backend.
     *
     * @param int $expectedSize - expected size of the object, in bytes
     * @param int $expectedWriteSize - expected size of writes to the object, in bytes
     * @param ?AllocHintFlag[] $flags
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setAllocHint(int $expectedSize, int $expectedWriteSize, ?array $flags = null): static
    {
        $flagValue = null;
        if ($flags !== null) {
            $flagValue = AllocHintFlag::combine($this->getIOContext()->getFFI(), ...$flags);
        }

        if ($flagValue === null) {
            RadosObjectException::handle($this->getIOContext()->getFFI()->rados_set_alloc_hint(
                $this->getIOContext()->getCData(),
                $this->getId(),
                $expectedSize,
                $expectedWriteSize
            ));
        } else {
            RadosObjectException::handle($this->getIOContext()->getFFI()->rados_set_alloc_hint2(
                $this->getIOContext()->getCData(),
                $this->getId(),
                $expectedSize,
                $expectedWriteSize,
                $flagValue
            ));
        }
        return $this;
    }

    /**
     * Binding for rados_lock_shared
     * Take a shared lock on an object.
     *
     * @param string $name - name of the lock
     * @param string $description - user-defined lock description
     * @param string $tag - tag of the lock
     * @param TimeValue|null $duration - duration of the lock. Set to NULL for infinite duration.
     * @param LockFlag[] $flags - lock flags
     * @param string|null $cookie - user-defined identifier for this instance of the lock
     * @return Lock
     * @throws RadosException
     * @throws RandomException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createSharedLock(
        string     $name,
        string     $description = "",
        string     $tag = "",
        ?TimeValue $duration = null,
        array      $flags = [],
        ?string    $cookie = null
    ): Lock
    {
        $durationValue = $duration?->createCData($this->getIOContext()->getFFI());
        $flagValue = LockFlag::combine(...$flags);

        do {
            $cookieValue = $cookie ?? Lock::generateCookieValue();
            $result = $this->getIOContext()->getFFI()->rados_lock_shared(
                $this->getIOContext()->getCData(), $this->getId(),
                $name, $cookieValue, $tag, $description,
                $durationValue ? FFI::addr($durationValue) : null,
                $flagValue
            );
        } while ($result === -Errno::EEXIST->value && $cookie === null);
        RadosObjectException::handle($result);

        return new Lock($this, $name, $cookieValue, $description, $tag, false, $duration);
    }

    /**
     * Binding for rados_lock_exclusive
     * Take an exclusive lock on an object.
     *
     * @param string $name - name of the lock
     * @param string $description - user-defined lock description
     * @param TimeValue|null $duration - duration of the lock. Set to NULL for infinite duration.
     * @param LockFlag[] $flags - lock flags
     * @param string|null $cookie - user-defined identifier for this instance of the lock
     * @return Lock
     * @throws RadosException
     * @throws RandomException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createExclusiveLock(
        string     $name,
        string     $description = "",
        ?TimeValue $duration = null,
        array      $flags = [],
        ?string    $cookie = null
    ): Lock
    {
        $durationValue = $duration?->createCData($this->getIOContext()->getFFI());
        $flagValue = LockFlag::combine(...$flags);

        do {
            $cookieValue = $cookie ?? Lock::generateCookieValue();
            $result = $this->getIOContext()->getFFI()->rados_lock_exclusive(
                $this->getIOContext()->getCData(), $this->getId(),
                $name, $cookieValue, $description,
                $durationValue ? FFI::addr($durationValue) : null,
                $flagValue
            );
        } while ($result === -Errno::EEXIST->value && $cookie === null);
        RadosObjectException::handle($result);

        return new Lock($this, $name, $cookieValue, $description, null, true, $duration);
    }

    /**
     * Binding for rados_list_lockers
     * List clients that have locked the named object lock and information about
     * the lock.
     *
     * @param string $name - name of the lock
     * @return ForeignLock[]
     * @throws RadosException
     * @throws RadosObjectException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function listLocks(string $name): array
    {
        $ffi = $this->getIOContext()->getFFI();
        $length = 1024;

        $tagsLength = $ffi->new('size_t');
        $clientsLength = $ffi->new('size_t');
        $cookiesLength = $ffi->new('size_t');
        $addressesLength = $ffi->new('size_t');

        $exclusive = $ffi->new('int');
        do {
            $tag = Buffer::create($ffi, $length);
            $clients = Buffer::create($ffi, $length);
            $cookies = Buffer::create($ffi, $length);
            $addresses = Buffer::create($ffi, $length);

            $tagsLength->cdata = $length;
            $clientsLength->cdata = $length;
            $cookiesLength->cdata = $length;
            $addressesLength->cdata = $length;

            $res = $this->getIOContext()->getFFI()->rados_list_lockers(
                $this->getIOContext()->getCData(),
                $this->getId(),
                $name, FFI::addr($exclusive),
                $tag->getCData(), FFI::addr($tagsLength),
                $clients->getCData(), FFI::addr($clientsLength),
                $cookies->getCData(), FFI::addr($cookiesLength),
                $addresses->getCData(), FFI::addr($addressesLength)
            );
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        RadosObjectException::handle($res);

        $clientList = $clients->readNullTerminatedStringList($clientsLength->cdata, false);
        $cookieList = $cookies->readNullTerminatedStringList($cookiesLength->cdata, false);
        $addressList = $addresses->readNullTerminatedStringList($addressesLength->cdata, false);

        $count = count($cookieList);
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            if (!isset($clientList[$i], $cookieList[$i], $addressList[$i])) {
                throw new RadosObjectException("Failed to read lock list");
            }

            $result[] = new ForeignLock(
                $this, $name,
                $cookieList[$i], $tag->readString($tagsLength->cdata),
                (bool) $exclusive->cdata,
                $clientList[$i], $addressList[$i]
            );
        }

        return $result;
    }

    /**
     * Rollback an object to a pool snapshot
     *
     * The contents of the object will be the same as
     * when the snapshot was taken.
     *
     * @param Snapshot $snapshot
     * @return $this
     * @throws RadosException
     */
    public function rollback(Snapshot $snapshot): static
    {
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_ioctx_snap_rollback(
            $this->getIOContext()->getCData(),
            $this->getId(),
            $snapshot->getName()
        ));
        return $this;
    }

    /**
     * Binding for rados_aio_read
     * Asynchronously read data from an object
     *
     * The io context determines the snapshot to read from, if any was set
     * by rados_ioctx_snap_set_read().
     *
     * @note Reusing the read buffer is only safe after the completion has been handled.
     *
     * @param int $length
     * @param int $offset
     * @param Buffer|null $readBuffer
     * @return ReadOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function readAsync(int $length, int $offset, ?Buffer $readBuffer = null): ReadOperationCompletion
    {
        if ($readBuffer !== null && $readBuffer->getSize() >= $length) {
            $buffer = $readBuffer;
        } else {
            $buffer = Buffer::create($this->getIOContext()->getFFI(), $length);
        }

        $completion = new ReadOperationCompletion($buffer, $this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_read(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(), $buffer->getCData(),
            $length, $offset
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_write
     * Write data to an object asynchronously
     *
     * @param string $buffer - data to write
     * @param int $offset - offset to start writing at
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeAsync(string $buffer, int $offset): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_write(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(), $buffer,
            strlen($buffer), $offset
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_append
     * Asynchronously append data to an object
     *
     * @param string $buffer - data to append
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function appendAsync(string $buffer): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_append(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(),
            $buffer, strlen($buffer)
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_write_full
     * Asynchronously write an entire object
     *
     * The object is filled with the provided data. If the object exists,
     * it is atomically truncated and then written.
     *
     * @param string $buffer - data to write
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeFullAsync(string $buffer): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_write_full(
            $this->getIOContext()->getCData(), $this->getId(),
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
     * @param string $buffer - data to write
     * @param int $writeLength - the total number of bytes to write
     * @param int $offset - byte offset in the object to begin writing at
     * @return WriteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function writeSameAsync(string $buffer, int $writeLength, int $offset): WriteOperationCompletion
    {
        $completion = new WriteOperationCompletion($this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_writesame(
            $this->getIOContext()->getCData(), $this->getId(),
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
     * @return RemoveOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function removeAsync(): RemoveOperationCompletion
    {
        $completion = new RemoveOperationCompletion($this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_remove(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData()
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_stat
     * Asynchronously get object stats (size/mtime)
     *
     * @return StatOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function statAsync(): StatOperationCompletion
    {
        $size = $this->getIOContext()->getFFI()->new("uint64_t");
        $mtime = $this->getIOContext()->getFFI()->new("time_t");
        $completion = new StatOperationCompletion($size, $mtime, $this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_stat(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(),
            FFI::addr($size), FFI::addr($mtime)
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_cmpext
     * Asynchronously compare an on-disk object range with a buffer
     *
     * @param string $compare - buffer containing bytes to be compared with object contents
     * @param int $offset - object byte offset at which to start the comparison
     * @return CompareOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function compareExtAsync(string $compare, int $offset): CompareOperationCompletion
    {
        $completion = new CompareOperationCompletion($this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_cmpext(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(),
            $compare, strlen($compare), $offset
        ));
        return $completion;
    }

    /**
     * Binding for rados_aio_getxattr
     * Asynchronously get the value of an extended attribute on an object.
     *
     * @param string $name - name of the attribute
     * @param int $maxLength - maximum length of the attribute value
     * @return GetXAttributeOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getXAttributeAsync(string $name, int $maxLength): GetXAttributeOperationCompletion
    {
        $buffer = Buffer::create($this->getIOContext()->getFFI(), $maxLength);
        $completion = new GetXAttributeOperationCompletion($buffer, $this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_getxattr(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(),
            $name, $buffer->getCData(), $maxLength
        ));
        return $completion;
    }

    /**
     * Asynchronously set an extended attribute on an object.
     *
     * @param string $name - name of the attribute
     * @param string $value - value of the attribute
     * @return SetXAttributeCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setXAttributeAsync(string $name, string $value): SetXAttributeCompletion
    {
        $completion = new SetXAttributeCompletion($this->getIOContext());
        RadosException::handle($this->getIOContext()->getFFI()->rados_aio_setxattr(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(),
            $name, $value, strlen($value)
        ));
        return $completion;
    }

    /**
     * Asynchronously delete an extended attribute from an object.
     *
     * @param string $name - name of the attribute
     * @return RemoveXAttributeCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function removeXAttributeAsync(string $name): RemoveXAttributeCompletion
    {
        $completion = new RemoveXAttributeCompletion($this->getIOContext());
        RadosException::handle($this->getIOContext()->getFFI()->rados_aio_rmxattr(
            $this->getIOContext()->getCData(), $this->getId(),
            $completion->getCData(), $name
        ));
        return $completion;
    }

    /**
     * Asynchronously get all extended attributes of an object as an associative array
     *
     * @return GetXAttributesOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getXAttributesAsync(): GetXAttributesOperationCompletion
    {
        $ffi = $this->getIOContext()->getFFI();
        $iterator = $ffi->new('rados_xattrs_iter_t');
        $completion = new GetXAttributesOperationCompletion($iterator, $this->getIOContext());
        RadosObjectException::handle($ffi->rados_aio_getxattrs(
            $this->getIOContext()->getCData(),
            $this->getId(),
            $completion->getCData(),
            FFI::addr($iterator)
        ));

        return $completion;
    }

    /**
     * Binding for rados_aio_exec
     * Asynchronously execute an OSD class method on an object
     *
     * The OSD has a plugin mechanism for performing complicated
     * operations on an object atomically. These plugins are called
     * classes. This function allows librados users to call the custom
     * methods. The input and output formats are defined by the class.
     * Classes in ceph.git can be found in src/cls subdirectories
     *
     * @param string $class - name of the class
     * @param string $method - name of the method
     * @param string $input - input data for the method
     * @param int $maxOutputSize - maximum size of the output buffer
     * @param Buffer|null $outputBuffer - Optional: temporary buffer to read into.
     * @return OsdClassMethodExecuteOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function executeAsync(string $class, string $method, string $input, int $maxOutputSize, ?Buffer $outputBuffer = null): OsdClassMethodExecuteOperationCompletion
    {
        if ($outputBuffer !== null && $outputBuffer->getSize() >= $maxOutputSize) {
            $buffer = $outputBuffer;
        } else {
            $buffer = Buffer::create($this->getIOContext()->getFFI(), $maxOutputSize);
        }
        $completion = new OsdClassMethodExecuteOperationCompletion($buffer, $this->getIOContext());
        RadosObjectException::handle($this->getIOContext()->getFFI()->rados_aio_exec(
            $this->getIOContext()->getCData(),
            $this->getId(),
            $completion->getCData(),
            $class, $method,
            $input, strlen($input),
            $buffer->getCData(), $maxOutputSize
        ));

        return $completion;
    }

    //TODO: read ops
    //TODO: write ops
}
