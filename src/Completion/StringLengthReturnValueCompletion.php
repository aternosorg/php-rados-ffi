<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Util\Buffer\Buffer;

/**
 * @extends ResultCompletion<string>
 */
class StringLengthReturnValueCompletion extends ResultCompletion
{
    /**
     * @param Buffer $buffer
     * @param IOContext $ioContext
     * @internal Completions are returned from async operations and should not be created manually
     */
    public function __construct(protected Buffer $buffer, IOContext $ioContext)
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
        return $this->buffer->readString($length);
    }
}
