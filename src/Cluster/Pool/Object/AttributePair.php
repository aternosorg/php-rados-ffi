<?php

namespace Aternos\Rados\Cluster\Pool\Object;

class AttributePair
{
    /**
     * @param string $key
     * @param string $value
     */
    public function __construct(
        protected string $key,
        protected string $value
    )
    {
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
