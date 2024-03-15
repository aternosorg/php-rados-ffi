<?php

namespace Aternos\Rados\Operation\Common\Task;

use Aternos\Rados\Constants\OMapComparisonOperator;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Common\CommonOperationTask;
use Aternos\Rados\Operation\Operation;
use FFI;
use FFI\CData;

/**
 * Ensure that an omap value satisfies a comparison,
 * with the supplied value on the right hand side (i.e.
 * for OP_LT, the comparison is actual_value < value)
 *
 * @extends CommonOperationTask<null>
 */
class OMapCompareTask extends CommonOperationTask
{
    protected ?CData $result = null;

    /**
     * @param string $key
     * @param string $value
     * @param OMapComparisonOperator $operator
     */
    public function __construct(
        protected string $key,
        protected string $value,
        protected OMapComparisonOperator $operator = OMapComparisonOperator::Equal
    )
    {
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): null
    {
        RadosObjectException::handle($this->result?->cdata ?? 0);
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $operation->getFFI()->{$this->getFunctionName($operation)}(
            $operation->getCData(),
            $this->key,
            $this->operator->getCValue($operation->getFFI()),
            $this->value,
            strlen($this->key),
            strlen($this->value),
            FFI::addr($this->result)
        );
    }

    /**
     * @inheritDoc
     */
    protected function getReadFunctionName(): string
    {
        return "rados_read_op_omap_cmp2";
    }

    /**
     * @inheritDoc
     */
    protected function getWriteFunctionName(): string
    {
        return "rados_write_op_omap_cmp2";
    }
}
