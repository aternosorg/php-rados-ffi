<?php

namespace Aternos\Rados\Completion;

use Aternos\Rados\Exception\CompletionException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Util\WrappedType;
use Closure;
use FFI;
use FFI\CData;

class Completion extends WrappedType
{
    /**
     * Binding for rados_aio_create_completion2
     * Constructs a completion to use with asynchronous operations
     *
     * @note This library currently does not support setting callbacks for completions
     *
     * @param FFI $ffi
     * @noinspection PhpUndefinedMethodInspection
     * @internal Completions are returned from async operations and should not be created manually
     */
    public function __construct(FFI $ffi)
    {
        $result = $ffi->new("rados_completion_t");
        $ffi->rados_aio_create_completion2(null, null, FFI::addr($result));
        parent::__construct($result, $ffi);
    }

    /**
     * Binding for rados_aio_wait_for_complete
     * Block until an operation completes
     * This means it is in memory on all replicas.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function waitForComplete(): static
    {
        $this->ffi->rados_aio_wait_for_complete($this->getCData());
        return $this;
    }

    /**
     * Binding for rados_aio_wait_for_complete
     * Block until an operation is safe
     * This means it is on stable storage on all replicas.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     * @deprecated
     */
    public function waitForSafe(): static
    {
        $this->ffi->rados_aio_wait_for_safe($this->getCData());
        return $this;
    }

    /**
     * Binding for rados_aio_is_complete
     * Has this asynchronous operation completed?
     *
     * @warning This does not imply that the complete callback has finished
     *
     * @return bool
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function isComplete(): bool
    {
        return (bool)$this->ffi->rados_aio_is_complete($this->getCData());
    }

    /**
     * Binding for rados_aio_is_safe
     * Is this asynchronous operation safe?
     *
     * @warning This does not imply that the safe callback has finished
     *
     * @return bool
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function isSafe(): bool
    {
        return (bool)$this->ffi->rados_aio_is_safe($this->getCData());
    }

    /**
     * Binding for rados_aio_wait_for_complete_and_cb
     * Block until an operation completes and callback completes
     * This means it is in memory on all replicas and can be read.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function waitForCompleteAndCallback(): static
    {
        $this->ffi->rados_aio_wait_for_complete_and_cb($this->getCData());
        return $this;
    }

    /**
     * Binding for rados_aio_wait_for_safe_and_cb
     * Block until an operation is safe and callback has completed
     * This means it is on stable storage on all replicas.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     * @deprecated
     */
    public function waitForSafeAndCallback(): static
    {
        $this->ffi->rados_aio_wait_for_safe_and_cb($this->getCData());
        return $this;
    }

    /**
     * Binding for rados_aio_is_complete_and_cb
     * Has this asynchronous operation and callback completed?
     *
     * @return bool
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function isCompleteAndCallbackDone(): bool
    {
        return (bool)$this->ffi->rados_aio_is_complete_and_cb($this->getCData());
    }

    /**
     * Binding for rados_aio_is_safe_and_cb
     * Is this asynchronous operation safe and has the callback completed?
     *
     * @return bool
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function isSafeAndCallbackDone(): bool
    {
        return (bool)$this->ffi->rados_aio_is_safe_and_cb($this->getCData());
    }

    /**
     * Binding for rados_aio_get_return_value
     * Get the return value of an asynchronous operation
     * The return value is set when the operation is complete or safe, whichever comes first.
     *
     * @pre The operation is safe or complete
     *
     * @return int
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function getReturnValue(): int
    {
        return $this->ffi->rados_aio_get_return_value($this->getCData());
    }

    /**
     * Binding for rados_aio_get_version
     * Get the internal object version of the target of an asynchronous operation
     *
     * @return int
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     */
    public function getVersion(): int
    {
        return $this->ffi->rados_aio_get_version($this->getCData());
    }

    /**
     * Binding for rados_aio_release
     * Release this completion
     *
     * Call this when you no longer need the completion. It may not be
     * freed immediately if the operation is not acked and committed.
     *
     * @return $this
     * @noinspection PhpUndefinedMethodInspection
     * @throws RadosException
     * @internal This method is called by the release method and should not be called manually
     */
    protected function releaseCompletion(): static
    {
        $this->ffi->rados_aio_release($this->getCData());
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->releaseCompletion();
    }
}
