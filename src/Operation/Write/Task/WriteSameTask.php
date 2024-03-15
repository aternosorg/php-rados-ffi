<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Write the same buffer multiple times
 *
 * @extends WriteOperationTask<null>
 */
class WriteSameTask extends WriteOperationTask
{
    /**
     * @param string $buffer
     * @param int $writeLength
     * @param int $offset
     */
    public function __construct(
        protected string $buffer,
        protected int $writeLength,
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
        $operation->getFFI()->rados_write_op_writesame(
            $operation->getCData(),
            $this->buffer,
            strlen($this->buffer),
            $this->writeLength,
            $this->offset
        );
    }
}
