<?php

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Exception\PoolException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer;
use Aternos\Rados\Util\WrappedType;
use FFI;
use FFI\CData;
use InvalidArgumentException;

class Pool extends WrappedType
{
    protected Cluster $cluster;
    protected ?string $name;
    protected ?int $id;

    /**
     * Binding for rados_pool_lookup
     * Lookup a pool id by name
     *
     * @param Cluster $cluster
     * @param string $name
     * @return int
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function lookup(Cluster $cluster, string $name): int
    {
        return PoolException::handle($cluster->getFFI()->rados_pool_lookup($cluster->getCData(), $name));
    }

    /**
     * Binding for rados_pool_reverse_lookup
     * Lookup a pool name by id
     *
     * @param Cluster $cluster
     * @param int $id
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function reverseLookup(Cluster $cluster, int $id): string
    {
        $step = 256;
        $length = $step;
        $ffi = $cluster->getFFI();
        do {
            $buffer = Buffer::create($ffi, $length);
            $res = $ffi->rados_pool_reverse_lookup($cluster->getCData(), $id, $buffer->getCData(), $length);
            $length += $step;
        } while ($res < 0 && -$res === Errno::ERANGE->value);
        $resultLength = PoolException::handle($res);
        return $buffer->readString($resultLength);
    }

    /**
     * @param Cluster $cluster
     * @param string|null $name
     * @param int|null $id
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(Cluster $cluster, ?string $name, ?int $id, CData $data, FFI $ffi)
    {
        if ($name === null && $id === null) {
            throw new InvalidArgumentException("Either name or id must be set");
        }
        $this->cluster = $cluster;
        $this->name = $name;
        $this->id = $id;
        parent::__construct($data, $ffi);
    }

    /**
     * @return Cluster
     */
    public function getCluster(): Cluster
    {
        return $this->cluster;
    }

    /**
     * Binding for rados_pool_lookup
     * Get the id of a pool
     *
     * @return int
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getId(): int
    {
        if ($this->id === null) {
            $this->id = static::lookup($this->getCluster(), $this->name);
        }
        return $this->id;
    }

    /**
     * Binding for rados_pool_reverse_lookup
     * Get the name of a pool
     *
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getName(): string
    {
        if ($this->name === null) {
            $this->name = static::reverseLookup($this->getCluster(), $this->id);
        }
        return $this->name;
    }

    /**
     * Binding for rados_pool_get_base_tier
     * Returns the pool that is the base tier for this pool.
     *
     * The return value is the ID of the pool that should be used to read from/write to.
     * If tiering is not set up for the pool, returns \c pool.
     *
     * @return int
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getBaseTier(): int
    {
        $result = $this->ffi->new("int64_t");
        PoolException::handle($this->ffi->rados_pool_get_base_tier($this->getCData(), $this->getId(), FFI::addr($result)));
        return $result->cdata;
    }

    /**
     * Binding for rados_pool_delete
     * Delete a pool and all data inside it
     *
     * The pool is removed from the cluster immediately,
     * but the actual data is deleted in the background.
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function delete(): static
    {
        PoolException::handle($this->ffi->rados_pool_delete($this->getCData(), $this->getName()));
        return $this;
    }

    /**
     * Binding for rados_ioctx_create/rados_ioctx_create2
     * Create an io context
     *
     * The io context allows you to perform operations within a particular pool.
     *
     * @return IOContext
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function createIOContext(): IOContext
    {
        $context = $this->ffi->new("rados_ioctx_t");
        if ($this->id !== null) {
            PoolException::handle($this->ffi->rados_ioctx_create2($this->getCData(), $this->id, FFI::addr($context)));
        } else {
            PoolException::handle($this->ffi->rados_ioctx_create($this->getCData(), $this->getName(), FFI::addr($context)));
        }
        return new IOContext($this, $context, $this->ffi);
    }
}
