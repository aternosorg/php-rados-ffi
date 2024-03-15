<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Cluster\Pool\Object\XAttributes\XAttributesIterator;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Read\ReadOperationTask;
use FFI;
use FFI\CData;
use RuntimeException;

/**
 * @extends ReadOperationTask<XAttributesIterator>
 */
class GetXAttributesTask extends ReadOperationTask
{
    protected ?CData $result = null;
    protected ?CData $iterator = null;

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): XAttributesIterator
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        RadosObjectException::handle($this->result->cdata);
        return new XAttributesIterator($this->iterator, $this->operation->getFFI());
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $this->result = $operation->getFFI()->new('int');
        $this->iterator = $operation->getFFI()->new('rados_xattrs_iter_t');

        $operation->getFFI()->rados_read_op_getxattrs(
            $operation->getCData(),
            FFI::addr($this->iterator),
            FFI::addr($this->result)
        );
    }
}
