<?php

namespace Aternos\Rados\Operation\Common\Task;

use Aternos\Rados\Operation\Common\CommonOperationTask;
use Aternos\Rados\Operation\Operation;

/**
 * Ensure that the object exists
 *
 * @extends CommonOperationTask<null>
 */
class AssertExistsTask extends CommonOperationTask
{
    /**
     * @inheritDoc
     */
    protected function parseResult(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function initTask(Operation $operation): void
    {
        $operation->getFFI()->{$this->getFunctionName($operation)}($operation->getCData());
    }

    /**
     * @inheritDoc
     */
    protected function getReadFunctionName(): string
    {
        return "rados_read_op_assert_exists";
    }

    /**
     * @inheritDoc
     */
    protected function getWriteFunctionName(): string
    {
        return "rados_write_op_assert_exists";
    }
}
