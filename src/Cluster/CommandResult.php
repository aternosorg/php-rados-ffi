<?php

namespace Aternos\Rados\Cluster;

use Aternos\Rados\Util\Buffer\Buffer;

class CommandResult
{
    /**
     * @param string $status
     * @param Buffer $output
     */
    public function __construct(
        protected string $status,
        protected Buffer $output
    )
    {
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return Buffer
     */
    public function getOutput(): Buffer
    {
        return $this->output;
    }
}
