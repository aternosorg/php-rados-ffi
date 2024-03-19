<?php

namespace Aternos\Rados\Operation\Write;

use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Completion\OperationCompletion;
use Aternos\Rados\Constants\OperationFlag;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Util\TimeSpec;
use FFI;

class WriteOperation extends Operation
{
    /**
     * @param FFI $ffi
     * @return static
     * @noinspection PhpUndefinedMethodInspection
     * @internal Use Rados::createWriteOperation instead
     */
    public static function create(FFI $ffi): static
    {
        $operation = $ffi->rados_create_write_op();
        return new static($operation, $ffi);
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function operate(RadosObject $object, ?TimeSpec $mtime = null, array $flags = []): array
    {
        $flagsValue = OperationFlag::combine($this->ffi, ...$flags);

        RadosObjectException::handle($this->ffi->rados_write_op_operate2(
            $this->getCData(),
            $object->getIOContext()->getCData(),
            $object->getId(),
            $mtime?->createCData($this->ffi),
            $flagsValue
        ));

        return $this->getTasks();
    }

    /**
     * @inheritDoc
     *
     * @note librados only accepts mtime values in seconds for async write operations, so nanoSeconds will be ignored
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function operateAsync(RadosObject $object, ?TimeSpec $mtime = null, array $flags = []): OperationCompletion
    {
        $flagsValue = OperationFlag::combine($this->ffi, ...$flags);
        $completion = new OperationCompletion($this->getTasks(), $object->getIOContext());

        RadosObjectException::handle($this->ffi->rados_aio_write_op_operate(
            $this->getCData(),
            $object->getIOContext()->getCData(),
            $completion->getCData(),
            $object->getId(),
            $mtime?->getSeconds(),
            $flagsValue
        ));

        return $completion;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function releaseCData(): void
    {
        $this->ffi->rados_release_write_op($this->getCData());
    }
}
