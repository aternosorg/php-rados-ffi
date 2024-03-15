<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;

/**
 * @extends ResultCompletion<null>
 */
class VoidErrorHandlingCompletion extends ResultCompletion
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
