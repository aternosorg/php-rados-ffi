<?php

namespace Aternos\Rados\Cluster\Pool\Snapshot;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\SnapshotException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer\Buffer;
use FFI;
use InvalidArgumentException;

class Snapshot implements SnapshotInterface
{
    protected ?int $timestamp = null;

    /**
     * Binding for rados_ioctx_snap_lookup
     * Get the id of a pool snapshot
     *
     * @param IOContext $ioContext
     * @param string $name
     * @return int
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function lookupName(IOContext $ioContext, string $name): int
    {
        $id = $ioContext->getFFI()->new("rados_snap_t");
        SnapshotException::handle($ioContext->getFFI()->rados_ioctx_snap_lookup($ioContext->getCData(), $name, FFI::addr($id)));
        return $id->cdata;
    }

    /**
     * Binding for rados_ioctx_snap_get_name
     * Get the name of a pool snapshot
     *
     * @param IOContext $ioContext
     * @param int $id
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function lookupId(IOContext $ioContext, int $id): string
    {
        $length = 256;
        $ffi = $ioContext->getFFI();
        do {
            $buffer = Buffer::create($ffi, $length);
            $res = $ffi->rados_ioctx_snap_get_name($ioContext->getCData(), $id, $buffer->getCData(), $length);
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        $resultLength = SnapshotException::handle($res);
        return $buffer->readString($resultLength);
    }

    /**
     * @param IOContext $ioContext
     * @param int|null $id
     * @param string|null $name
     */
    public function __construct(
        protected IOContext $ioContext,
        protected ?int $id,
        protected ?string $name
    )
    {
        if ($this->id === null && $this->name === null) {
            throw new InvalidArgumentException("Either id or name must be set");
        }
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function getId(): int
    {
        if ($this->id === null) {
            $this->id = static::lookupName($this->ioContext, $this->name);
        }
        return $this->id;
    }

    /**
     * Get the name of the snapshot
     *
     * @return string|null
     * @throws RadosException
     */
    public function getName(): ?string
    {
        if ($this->name === null) {
            $this->name = static::lookupId($this->ioContext, $this->id);
        }
        return $this->name;
    }

    /**
     * Binding for rados_ioctx_snap_get_stamp
     * Find when a pool snapshot occurred
     *
     * @return int
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getTimeStamp(): int
    {
        if ($this->timestamp !== null) {
            return $this->timestamp;
        }

        $result = $this->ioContext->getFFI()->new("time_t");
        SnapshotException::handle($this->ioContext->getFFI()->rados_ioctx_snap_get_stamp(
            $this->ioContext->getCData(),
            $this->getId(),
            FFI::addr($result)
        ));
        return $this->timestamp = $result->cdata;
    }

    /**
     * Binding for rados_ioctx_snap_remove
     * @inheritDoc
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function remove(): static
    {
        SnapshotException::handle($this->ioContext->getFFI()->rados_ioctx_snap_remove($this->ioContext->getCData(), $this->getName()));
        return $this;
    }
}
