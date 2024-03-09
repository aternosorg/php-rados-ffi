<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Util\Buffer;
use FFI;

/**
 * @extends OperationCompletion<string>
 */
class ReadOperationCompletion extends OperationCompletion
{
    /**
     * @param Buffer $readBuffer
     * @param IOContext $ioContext
     */
    public function __construct(protected Buffer $readBuffer, IOContext $ioContext)
    {
        parent::__construct($ioContext);
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult()
    {
        $length = CompletionException::handle($this->getReturnValue());
        return $this->readBuffer->readString($length);
    }
}
