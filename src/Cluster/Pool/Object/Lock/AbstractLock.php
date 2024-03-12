<?php

namespace Aternos\Rados\Cluster\Pool\Object\Lock;

use Aternos\Rados\Cluster\Pool\IOContext;
use Aternos\Rados\Cluster\Pool\Object\RadosObject;

class AbstractLock
{
    protected IOContext $ioContext;

    /**
     * @param RadosObject $object
     * @param string $name
     * @param string $cookie
     * @param string|null $tag
     * @param bool $exclusive
     */
    public function __construct(
        protected RadosObject $object,
        protected string $name,
        protected string $cookie,
        protected ?string $tag,
        protected bool $exclusive
    )
    {
        $this->ioContext = $object->getIoContext();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCookie(): string
    {
        return $this->cookie;
    }

    /**
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @return RadosObject
     */
    public function getObject(): RadosObject
    {
        return $this->object;
    }

    /**
     * @return IOContext
     */
    public function getIoContext(): IOContext
    {
        return $this->ioContext;
    }
}
