<?php

namespace Aternos\Rados\Operation\Read;

use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Completion\OperationCompletion;
use Aternos\Rados\Constants\OperationFlag;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Util\TimeSpec;
use FFI;

class ReadOperation extends Operation
{
    /**
     * @param FFI $ffi
     * @return static
     * @noinspection PhpUndefinedMethodInspection
     * @internal Use Rados::createReadOperation instead
     */
    public static function create(FFI $ffi): static
    {
        $operation = $ffi->rados_create_read_op();
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

        RadosObjectException::handle($this->ffi->rados_read_op_operate(
            $this->getCData(),
            $object->getIOContext()->getCData(),
            $object->getId(),
            $flagsValue
        ));

        return $this->getTasks();
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function operateAsync(RadosObject $object, ?TimeSpec $mtime = null, array $flags = []): OperationCompletion
    {
        $flagsValue = OperationFlag::combine($this->ffi, ...$flags);
        $completion = new OperationCompletion($this->getTasks(), $object->getIOContext());

        RadosObjectException::handle($this->ffi->rados_aio_read_op_operate(
            $this->getCData(),
            $object->getIOContext()->getCData(),
            $completion->getCData(),
            $object->getId(),
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
        $this->ffi->rados_release_read_op($this->getCData());
    }
}
