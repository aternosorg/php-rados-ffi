<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Read\ReadOperationTask;
use Aternos\Rados\Util\Buffer\Buffer;
use FFI;
use FFI\CData;
use RuntimeException;

/**
 * @extends ReadOperationTask<string>
 */
class ReadTask extends ReadOperationTask
{
    protected ?CData $bytesRead = null;
    protected ?CData $result = null;

    public function __construct(
        protected int $length,
        protected int $offset,
        protected ?Buffer $readBuffer = null
    )
    {
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): string
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        RadosObjectException::handle($this->result->cdata);
        return $this->readBuffer->readString($this->bytesRead->cdata);
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');

        if ($this->readBuffer === null || $this->readBuffer->getSize() < $this->length) {
            $this->readBuffer = Buffer::create($operation->getFFI(), $this->length);
        }

        $operation->getFFI()->rados_read_op_read(
            $operation->getCData(),
            $this->offset,
            $this->length,
            $this->readBuffer->getCData(),
            FFI::addr($this->bytesRead),
            FFI::addr($this->result)
        );
    }
}
