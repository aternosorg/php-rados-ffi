<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Write whole object, atomically replacing it.
 *
 * @extends WriteOperationTask<null>
 */
class WriteFullTask extends WriteOperationTask
{
    /**
     * @param string $buffer
     */
    public function __construct(
        protected string $buffer
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
        $operation->getFFI()->rados_write_op_write_full(
            $operation->getCData(),
            $this->buffer,
            strlen($this->buffer)
        );
    }
}
