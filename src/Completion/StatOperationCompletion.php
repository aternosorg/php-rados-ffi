<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\ObjectStat;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use FFI\CData;

class StatOperationCompletion extends OperationCompletion
{
    /**
     * @param CData $size
     * @param CData $mTime
     * @param IOContext $ioContext
     */
    public function __construct(protected CData $size, protected CData $mTime, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult()
    {
        CompletionException::handle($this->getReturnValue());
        return new ObjectStat($this->size->cdata, $this->mTime->cdata);
    }
}
