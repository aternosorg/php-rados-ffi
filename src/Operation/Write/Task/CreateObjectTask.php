<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Constants\CreateMode;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Create the object
 *
 * @extends WriteOperationTask<null>
 */
class CreateObjectTask extends WriteOperationTask
{
    /**
     * @param CreateMode $exclusive
     */
    public function __construct(
        protected CreateMode $exclusive
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
        $operation->getFFI()->rados_write_op_create(
            $operation->getCData(),
            $this->exclusive->value,
            ""
        );
    }
}
