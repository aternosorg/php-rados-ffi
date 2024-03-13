<?php

namespace Aternos\Rados\Cluster\Pool\Snapshot;

interface SnapshotInterface
{
    /**
     * Snapshot id
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Remove the snapshot
     *
     * @return $this
     */
    public function remove(): static;
}
