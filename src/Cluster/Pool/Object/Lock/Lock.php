<?php

namespace Aternos\Rados\Cluster\Pool\Object\Lock;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Completion\UnlockOperationCompletion;
use Aternos\Rados\Constants\LockFlag;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Util\TimeValue;
use FFI;
use Random\RandomException;

class Lock extends AbstractLock
{
    /**
     * @return string
     * @throws RandomException
     */
    static function generateCookieValue(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param RadosObject $object
     * @param string $name
     * @param string $cookie
     * @param string $description
     * @param string|null $tag
     * @param bool $exclusive
     * @param TimeValue|null $duration
     */
    public function __construct(
        RadosObject $object,
        string $name,
        string $cookie,
        protected string $description,
        ?string $tag,
        bool $exclusive,
        protected ?TimeValue $duration
    )
    {
        parent::__construct($object, $name, $cookie, $tag, $exclusive);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Binding for rados_unlock
     * Release a shared or exclusive lock on an object.
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unlock(): static
    {
        RadosObjectException::handle($this->ioContext->getFFI()->rados_unlock(
            $this->ioContext->getCData(), $this->object->getId(),
            $this->name, $this->cookie
        ));
        $this->setLocked(false);
        return $this;
    }

    /**
     * Binding for rados_aio_unlock
     * Asynchronous release a shared or exclusive lock on an object.
     *
     * @return UnlockOperationCompletion
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function unlockAsync(): UnlockOperationCompletion
    {
        $completion = new UnlockOperationCompletion($this->ioContext);
        RadosObjectException::handle($this->getIoContext()->getFFI()->rados_aio_unlock(
            $this->ioContext->getCData(), $this->getObject()->getId(),
            $this->name, $this->cookie,
            $completion->getCData()
        ));
        return $completion;
    }

    /**
     * Renew this lock
     *
     * @param TimeValue|null $duration - If null, the original duration will be used
     * @param bool $mustRenew - If true, the operation will fail if the original lock has expired,
     * otherwise, a new lock will be created in that case
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function renew(?TimeValue $duration = null, bool $mustRenew = false): static
    {
        if ($duration === null) {
            $duration = $this->duration;
        }

        $durationValue = $duration?->createCData($this->getIoContext()->getFFI());
        $flags = $mustRenew ? LockFlag::MUST_RENEW->value : LockFlag::MAY_RENEW->value;
        if ($this->isExclusive()) {
            RadosObjectException::handle($this->getIOContext()->getFFI()->rados_lock_exclusive(
                $this->getIOContext()->getCData(), $this->getObject()->getId(),
                $this->name, $this->cookie, $this->description,
                $durationValue ? FFI::addr($durationValue) : null,
                $flags
            ));
        } else {
            RadosObjectException::handle($this->getIOContext()->getFFI()->rados_lock_shared(
                $this->getIOContext()->getCData(), $this->getObject()->getId(),
                $this->name, $this->cookie, $this->tag, $this->description,
                $durationValue ? FFI::addr($durationValue) : null,
                $flags
            ));
        }

        return $this;
    }
}
