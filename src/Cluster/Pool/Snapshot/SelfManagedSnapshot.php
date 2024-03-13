<?php

namespace Aternos\Rados\Cluster\Pool\Snapshot;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Snapshot\SnapshotInterface;
use Aternos\Rados\Completion\SelfManagedSnapshotRemoveOperationCompletion;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\SnapshotException;

class SelfManagedSnapshot implements SnapshotInterface
{
    public function __construct(protected IOContext $ioContext, protected int $id)
    {
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Bindings for rados_ioctx_selfmanaged_snap_create
     * Remove a self-managed snapshot
     *
     * This increases the snapshot sequence number, which will cause
     * snapshots to be removed lazily.
     *
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function remove(): static
    {
        SnapshotException::handle($this->ioContext->getFFI()->rados_ioctx_selfmanaged_snap_remove(
            $this->ioContext->getCData(),
            $this->getId()
        ));
        return $this;
    }

    /**
     * @return SelfManagedSnapshotRemoveOperationCompletion
     * @throws RadosException
     */
    public function removeAsync(): SelfManagedSnapshotRemoveOperationCompletion
    {
        $completion = new SelfManagedSnapshotRemoveOperationCompletion($this->ioContext);
        SnapshotException::handle($this->ioContext->getFFI()->rados_aio_ioctx_selfmanaged_snap_remove(
            $this->ioContext->getCData(),
            $this->getId(),
            $completion->getCData()
        ));
        return $completion;
    }
}
