<?php

namespace Aternos\Rados\Operation\Common\Task;

use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Common\CommonOperationTask;
use Aternos\Rados\Operation\Operation;
use FFI;
use FFI\CData;

/**
 * Ensure that given object range (extent) satisfies comparison.
 *
 * @extends CommonOperationTask<true|int>
 */
class CompareExtTask extends CommonOperationTask
{
    protected ?CData $result = null;

    /**
     * @param string $buffer - buffer containing bytes to be compared with object contents
     * @param int $offset - object byte offset at which to start the comparison
     */
    public function __construct(
        protected string $buffer,
        protected int $offset
    )
    {
    }

    /**
     * @inheritDoc
     * @throws RadosException
     * @return true|int - true on match, offset of mismatch on failure
     */
    protected function parseResult(): int
    {
        $result = RadosObjectException::handle($this->result?->cdata ?? 0);
        if ($result === 0) {
            return true;
        }
        return -$result - Constants::MAX_ERRNO;
    }

    /**
     * @inheritDoc
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $operation->getFFI()->{$this->getFunctionName($operation)}(
            $operation->getCData(),
            $this->buffer,
            strlen($this->buffer),
            $this->offset,
            FFI::addr($this->result)
        );
    }

    /**
     * @inheritDoc
     */
    protected function getReadFunctionName(): string
    {
        return "rados_read_op_cmpext";
    }

    /**
     * @inheritDoc
     */
    protected function getWriteFunctionName(): string
    {
        return "rados_write_op_cmpext";
    }
}
