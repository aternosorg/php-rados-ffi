<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Constants\Constants;
use Aternos\Rados\Exception\RadosException;

/**
 * @extends OperationCompletion<true|int>
 */
class CompareOperationCompletion extends OperationCompletion
{
    /**
     * @inheritDoc
     * @throws RadosException
     */
    public function parseResult()
    {
        $result = $this->getReturnValue();
        if ($result === 0) {
            return true;
        }
        return -$result - Constants::MAX_ERRNO;
    }
}
