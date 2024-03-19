<?php

namespace Aternos\Rados\Cluster;

class CommandResult
{
    /**
     * @param string $status
     * @param string $output
     */
    public function __construct(
        protected string $status,
        protected string $output
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
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }
}
