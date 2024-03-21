<?php

namespace Aternos\Rados\Operation\Common\Task;

use Aternos\Rados\Operation\Common\CommonOperationTask;
use Aternos\Rados\Operation\Operation;

/**
 * Ensure that the object exists and that its internal version
 * number is equal to "ver". "ver" should be a
 * version number previously obtained with rados_get_last_version().
 * - If the object's version is greater than the asserted version
 *   then rados_read_op_operate will return -ERANGE instead of
 *   executing the op.
 * - If the object's version is less than the asserted version
 *   then rados_read_op_operate will return -EOVERFLOW instead
 *   of executing the op.
 *
 * @extends CommonOperationTask<null>
 */
class AssertVersionTask extends CommonOperationTask
{
    /**
     * @param int $version
     */
    public function __construct(protected int $version)
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
     */
    protected function initTask(Operation $operation): void
    {
        $operation->getFFI()->{$this->getFunctionName($operation)}($operation->getCData(), $this->version);
    }

    /**
     * @inheritDoc
     */
    protected function getReadFunctionName(): string
    {
        return "rados_read_op_assert_version";
    }

    /**
     * @inheritDoc
     */
    protected function getWriteFunctionName(): string
    {
        return "rados_write_op_assert_version";
    }
}
