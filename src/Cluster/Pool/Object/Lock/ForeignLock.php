<?php

namespace Aternos\Rados\Cluster\Pool\Object\Lock;

use Aternos\Rados\Cluster\Pool\Object\RadosObject;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;

class ForeignLock extends AbstractLock
{
    /**
     * @param RadosObject $object
     * @param string $name
     * @param string $cookie
     * @param string|null $tag
     * @param bool $exclusive
     * @param string $client
     * @param string $address
     */
    public function __construct(
        RadosObject $object,
        string $name,
        string $cookie,
        ?string $tag,
        bool $exclusive,
        protected string $client,
        protected string $address
    )
    {
        parent::__construct($object, $name, $cookie, $tag, $exclusive);
    }

    /**
     * @return string
     */
    public function getClient(): string
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Binding for rados_break_lock
     * Releases a shared or exclusive lock on an object, which was taken by the
     * specified client.
     *
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function break(): static
    {
        RadosObjectException::handle($this->getIoContext()->getFFI()->rados_break_lock(
            $this->getIoContext()->getCData(),
            $this->getObject()->getId(),
            $this->getName(),
            $this->getClient(),
            $this->getCookie()
        ));

        return $this;
    }
}
