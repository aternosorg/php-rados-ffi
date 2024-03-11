<?php

namespace Aternos\Rados\Cluster\Pool\ObjectIterator;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;

class ObjectEntry
{
    protected ?RadosObject $object = null;

    /**
     * @param IOContext $ioContext
     * @param string $entry
     * @param string|null $key
     * @param string|null $namespace
     */
    public function __construct(
        protected IOContext $ioContext,
        protected string    $entry,
        protected ?string   $key,
        protected ?string   $namespace
    )
    {
    }

    /**
     * @return RadosObject
     */
    public function getObject(): RadosObject
    {
        if ($this->object === null) {
            $this->object = $this->ioContext->getObject($this->getEntry());
        }
        return $this->object;
    }

    /**
     * @return string
     */
    public function getEntry(): string
    {
        return $this->entry;
    }

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}
