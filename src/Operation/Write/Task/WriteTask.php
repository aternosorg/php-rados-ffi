<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Write to offset
 *
 * @extends WriteOperationTask<null>
 */
class WriteTask extends WriteOperationTask
{
    /**
     * @param string $buffer
     * @param int $offset
     */
    public function __construct(
        protected string $buffer,
        protected int $offset
    )
    {
    }

    /**
     * @inheritDoc
     */
    protected function parseResult(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $operation->getFFI()->rados_write_op_write(
            $operation->getCData(),
            $this->buffer,
            strlen($this->buffer),
            $this->offset
        );
    }
}
