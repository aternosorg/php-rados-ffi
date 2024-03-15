<?php

namespace Aternos\Rados\Operation\Write;

use Aternos\Rados\Constants\OperationFlag;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\OperationTask;
use RuntimeException;

abstract class WriteOperationTask extends OperationTask
{
    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function appendToOperation(Operation $operation): void
    {
        if (!($operation instanceof WriteOperation)) {
            throw new RuntimeException("WriteOperationTask can only be appended to a WriteOperation");
        }
        $this->initTask($operation);
        $operation->getFFI()->rados_write_op_set_flags(
            $operation->getCData(),
            OperationFlag::combine($operation->getFFI(), ...$this->getFlags())
        );
    }

    /**
     * @param Operation $operation
     * @return void
     */
    abstract protected function initTask(Operation $operation): void;
}
