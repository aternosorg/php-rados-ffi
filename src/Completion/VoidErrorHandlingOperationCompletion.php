<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;

/**
 * @extends OperationCompletion<null>
 */
class VoidErrorHandlingOperationCompletion extends OperationCompletion
{
    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult()
    {
        CompletionException::handle($this->getReturnValue());
        return null;
    }
}
