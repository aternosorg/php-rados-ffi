<?php

namespace Aternos\Rados\Cluster\Pool\Object;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Completion\CompareOperationCompletion;
use Aternos\Rados\Completion\ReadOperationCompletion;
use Aternos\Rados\Completion\RemoveOperationCompletion;
use Aternos\Rados\Completion\StatOperationCompletion;
use Aternos\Rados\Completion\WriteOperationCompletion;
use Aternos\Rados\Completion\XAttributes\GetXAttributeOperationCompletion;
use Aternos\Rados\Completion\XAttributes\GetXAttributesOperationCompletion;
use Aternos\Rados\Completion\XAttributes\RemoveXAttributeCompletion;
use Aternos\Rados\Completion\XAttributes\SetXAttributeCompletion;
use Aternos\Rados\Constants\ChecksumType;
use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use FFI;
use InvalidArgumentException;

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
        $step = 512;
        $length = $step;
        do {
            $buffer = Buffer::create($this->getIOContext()->getFFI(), $length);
            $res = $this->getIOContext()->getFFI()->rados_getxattr(
                $this->getIOContext()->getCData(), $this->getId(),
                $name, $buffer->getCData(), $length
            );
            $length += $step;
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

    //TODO: read ops
    //TODO: write ops
}
