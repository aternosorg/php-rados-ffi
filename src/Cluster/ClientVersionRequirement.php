<?php

namespace Aternos\Rados\Cluster;

class ClientVersionRequirement
{
    protected int $minCompatibleClient;
    protected int $requiredMinCompatibleClient;

    /**
     * @param int $minCompatibleClient
     * @param int $requiredMinCompatibleClient
     */
    public function __construct(int $minCompatibleClient, int $requiredMinCompatibleClient)
    {
        $this->minCompatibleClient = $minCompatibleClient;
        $this->requiredMinCompatibleClient = $requiredMinCompatibleClient;
    }

    /**
     * Minimum compatible client version based upon the current features
     *
     * @return int
     */
    public function getMinCompatibleClient(): int
    {
        return $this->minCompatibleClient;
    }

    /**
     * Required minimum client version based upon explicit setting
     *
     * @return int
     */
    public function getRequiredMinCompatibleClient(): int
    {
        return $this->requiredMinCompatibleClient;
    }
}
