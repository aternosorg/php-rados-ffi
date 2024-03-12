<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\OsdClassMethodExecuteResult;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Util\Buffer;

/**
 * @extends OperationCompletion<OsdClassMethodExecuteResult>
 */
class OsdClassMethodExecuteOperationCompletion extends OperationCompletion
{
    /**
     * @param Buffer $outputBuffer
     * @param IOContext $ioContext
     * @internal Completions are returned from async operations and should not be created manually
     */
    public function __construct(protected Buffer $outputBuffer, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult()
    {
        return new OsdClassMethodExecuteResult(
            $this->getReturnValue(),
            $this->outputBuffer
        );
    }
}
