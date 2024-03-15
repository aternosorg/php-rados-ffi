<?php

namespace Aternos\Rados\Operation\Read;

use Aternos\Rados\Constants\OperationFlag;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\OperationTask;
use RuntimeException;

abstract class ReadOperationTask extends OperationTask
{
    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function appendToOperation(Operation $operation): void
    {
        if (!($operation instanceof ReadOperation)) {
            throw new RuntimeException("ReadOperationTask can only be appended to a ReadOperation");
        }
        $this->initTask($operation);
        $operation->getFFI()->rados_read_op_set_flags(
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
