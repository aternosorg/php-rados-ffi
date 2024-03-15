<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Constants\ChecksumType;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Read\ReadOperationTask;
use Aternos\Rados\Util\Buffer\Buffer;
use FFI;
use FFI\CData;
use InvalidArgumentException;
use RuntimeException;

/**
 * Compute checksum from object data
 *
 * @extends ReadOperationTask<int[]>
 */
class ChecksumTask extends ReadOperationTask
{
    protected ?CData $result = null;
    protected ?Buffer $buffer = null;

    /**
     * @param ChecksumType $type - checksum algorithm to utilize
     * @param int $initValue - initial value for the checksum
     * @param int $length - length of the data to checksum
     * @param int $offset - offset of the data to checksum
     * @param int|null $chunkSize - optional length-aligned chunk size for checksums
     */
    public function __construct(
        protected ChecksumType $type,
        protected int $initValue,
        protected int $length,
        protected int $offset,
        protected ?int $chunkSize = null
    )
    {
        if ($this->chunkSize === null) {
            $this->chunkSize = $this->length;
        }

        if ($this->chunkSize <= 0) {
            throw new InvalidArgumentException("Chunk size must be greater than 0");
        }
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): array
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        RadosObjectException::handle($this->result->cdata);
        return $this->type->unpack($this->buffer->toString());
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');

        $checksumLength = $this->type->getLength();
        $initString = $this->type->createInitString($this->initValue);

        $resultCount = ceil($this->length / $this->chunkSize);
        $resultLength = $resultCount * $checksumLength + 4;

        $this->buffer = Buffer::create($operation->getFFI(), $resultLength);

        $operation->getFFI()->rados_read_op_checksum(
            $operation->getCData(),
            $this->type->getCValue($operation->getFFI()),
            $initString, $checksumLength,
            $this->offset, $this->length, $this->chunkSize,
            $this->buffer->getCData(), $resultLength,
            FFI::addr($this->result)
        );
    }
}
