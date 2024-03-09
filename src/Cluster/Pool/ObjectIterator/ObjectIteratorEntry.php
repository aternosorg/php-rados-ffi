<?php

namespace Aternos\Rados\Cluster\Pool\ObjectIterator;

class ObjectIteratorEntry
{
    /**
     * @param string $entry
     * @param string|null $key
     * @param string|null $namespace
     */
    public function __construct(protected string $entry, protected ?string $key, protected ?string $namespace)
    {
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
