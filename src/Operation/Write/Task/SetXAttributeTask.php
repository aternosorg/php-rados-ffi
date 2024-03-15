<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;

/**
 * Set an xattr
 *
 * @extends WriteOperationTask<null>
 */
class SetXAttributeTask extends WriteOperationTask
{
    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(
        protected string $name,
        protected string $value
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
        $operation->getFFI()->rados_write_op_setxattr(
            $operation->getCData(),
            $this->name,
            $this->value,
            strlen($this->value)
        );
    }
}
