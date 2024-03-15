<?php

namespace Aternos\Rados\Cluster\Pool\Object;

use Aternos\Rados\Util\Buffer\Buffer;

class OsdClassMethodExecuteResult
{
    /**
     * @param int $returnValue
     * @param Buffer $output
     */
    public function __construct(
        protected int $returnValue,
        protected Buffer $output
    )
    {
    }

    /**
     * the length of the output, or -ERANGE if the output buffer does not have
     * enough space to store it (For methods that return data).
     * For methods that don't return data, the return value is method-specific.
     *
     * @return int
     */
    public function getReturnValue(): int
    {
        return $this->returnValue;
    }

    /**
     * The output buffer of the method.
     *
     * @return Buffer
     */
    public function getOutput(): Buffer
    {
        return $this->output;
    }
}
