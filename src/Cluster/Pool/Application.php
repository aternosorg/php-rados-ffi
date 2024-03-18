<?php

namespace Aternos\Rados\Cluster\Pool;

use Aternos\Rados\Exception\ApplicationException;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Generated\Errno;
use Aternos\Rados\Util\Buffer\Buffer;
use FFI;

class Application
{
    /**
     * @param IOContext $ioContext
     * @param string $name
     */
    public function __construct(
        protected IOContext $ioContext,
        protected string $name
    )
    {
    }

    /**
     * Binding for rados_application_enable
     * Enable an application on a pool
     *
     * @param bool $force
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function enabled(bool $force): static
    {
        ApplicationException::handle($this->ioContext->getFFI()->rados_application_enable(
            $this->ioContext->getCData(),
            $this->getName(),
            $force ? 1 : 0
        ));
        return $this;
    }

    /**
     * Get the name of the application
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Binding for rados_application_metadata_get
     * Get application metadata value from pool
     *
     * @param string $key
     * @param string $value
     * @return string
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getMetadata(string $key, string $value): string
    {
        $valueLength = $this->ioContext->getFFI()->new("size_t");
        $length = 512;
        do {
            $buffer = Buffer::create($this->ioContext->getFFI(), $length);
            $valueLength->cdata = $length;
            $res = $this->ioContext->getFFI()->rados_application_metadata_get(
                $this->ioContext->getCData(),
                $this->getName(),
                $key,
                $buffer->getCData(),
                FFI::addr($valueLength)
            );
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        ApplicationException::handle($res);

        return $buffer->toString();
    }

    /**
     * Binding for rados_application_metadata_set
     * Set application metadata on a pool
     *
     * @param string $key
     * @param string $value
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function setMetadata(string $key, string $value): static
    {
        ApplicationException::handle($this->ioContext->getFFI()->rados_application_metadata_set(
            $this->ioContext->getCData(),
            $this->getName(),
            $key,
            $value
        ));
        return $this;
    }

    /**
     * Binding for rados_application_metadata_remove
     * Remove application metadata from a pool
     *
     * @param string $key
     * @return $this
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function removeMetadata(string $key): static
    {
        ApplicationException::handle($this->ioContext->getFFI()->rados_application_metadata_remove(
            $this->ioContext->getCData(),
            $this->getName(),
            $key
        ));
        return $this;
    }

    /**
     * Binding for rados_application_metadata_list
     * List all metadata key/value pairs associated with an application.
     *
     * @return string[] - key/value pairs as an associative array
     * @throws RadosException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function listMetadata(): array
    {
        $keysLength = $this->ioContext->getFFI()->new("size_t");
        $valuesLength = $this->ioContext->getFFI()->new("size_t");
        $length = 1024;
        do {
            $keyBuffer = Buffer::create($this->ioContext->getFFI(), $length);
            $valueBuffer = Buffer::create($this->ioContext->getFFI(), $length);
            $keysLength->cdata = $length;
            $valuesLength->cdata = $length;
            $res = $this->ioContext->getFFI()->rados_application_metadata_list(
                $this->ioContext->getCData(),
                $this->getName(),
                $keyBuffer->getCData(),
                FFI::addr($keysLength),
                $valueBuffer->getCData(),
                FFI::addr($valuesLength)
            );
            $length = Buffer::grow($length);
        } while (-$res === Errno::ERANGE->value);
        ApplicationException::handle($res);

        $keys = $keyBuffer->readNullTerminatedStringList($keysLength->cdata, false);
        $values = $valueBuffer->readNullTerminatedStringList($valuesLength->cdata, false);

        $result = [];
        foreach ($keys as $i => $key) {
            $result[$key] = $values[$i];
        }

        return $result;
    }
}
