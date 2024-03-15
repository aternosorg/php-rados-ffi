<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Remove an xattr
 *
 * @extends WriteOperationTask<null>
 */
class RemoveXAttributeTask extends WriteOperationTask
{
    public function __construct(
        protected string $name
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
        $operation->getFFI()->rados_write_op_rmxattr(
            $operation->getCData(),
            $this->name
        );
    }
}
