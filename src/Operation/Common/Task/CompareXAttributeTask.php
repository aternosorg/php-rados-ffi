<?php

namespace Aternos\Rados\Operation\Common\Task;

use Aternos\Rados\Constants\XAttributeComparisonOperator;
use Aternos\Rados\Operation\Common\CommonOperationTask;
use Aternos\Rados\Operation\Operation;

/**
 * Ensure that given xattr satisfies comparison,
 * with the supplied value on the left hand side (i.e.
 * for OP_LT, the comparison is value < actual_value)
 *
 * If the comparison is not satisfied, the return code of the
 * operation will be -ECANCELED
 *
 * @extends CommonOperationTask<null>
 */
class CompareXAttributeTask extends CommonOperationTask
{
    /**
     * @param string $name
     * @param string $value
     * @param XAttributeComparisonOperator $operator
     */
    public function __construct(
        protected string $name,
        protected string $value,
        protected XAttributeComparisonOperator $operator = XAttributeComparisonOperator::Equal
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
     */
    protected function initTask(Operation $operation): void
    {
        $operation->getFFI()->{$this->getFunctionName($operation)}(
            $operation->getCData(),
            $this->name,
            $this->operator->getCValue($operation->getFFI()),
            $this->value,
            strlen($this->value)
        );
    }

    /**
     * @inheritDoc
     */
    protected function getReadFunctionName(): string
    {
        return "rados_read_op_cmpxattr";
    }

    /**
     * @inheritDoc
     */
    protected function getWriteFunctionName(): string
    {
        return "rados_write_op_cmpxattr";
    }
}
