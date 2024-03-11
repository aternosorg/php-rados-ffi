<?php

namespace Aternos\Rados;

use Aternos\Rados\Cluster\Cluster;
use Aternos\Rados\Exception\RadosException;
use FFI;

class Rados
{
    const DEFAULT_FFI_SCOPE = "PHP_RADOS_FFI";

    protected static ?Rados $instance = null;
    protected bool $initialized = false;
    protected ?FFI $ffi = null;
    protected string $headerPath = __DIR__ . "/../includes/librados.h";

    /**
     * @return Rados
     */
    public static function getInstance(): Rados
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function getHeaderPath(): string
    {
        return $this->headerPath;
    }

    /**
     * @param string $headerPath
     * @return $this
     */
    public function setHeaderPath(string $headerPath): Rados
    {
        $this->headerPath = $headerPath;
        return $this;
    }

    /**
     * @return string
     */
    protected function readHeaders(): string
    {
        return file_get_contents($this->headerPath);
    }

    /**
     * Initialize Rados
     *
     * @return Rados
     */
    public function initialize(): static
    {
        if ($this->initialized) {
            return $this;
        }

        $this->ffi = FFI::load($this->headerPath);
        $this->initialized = true;
        return $this;
    }

    /**
     * Initiate Rados from preloaded headers
     * This requires FFI preloading to be enabled and $rados->initialize()
     * to be called in the opcache.preload file
     *
     * See https://www.php.net/manual/en/ffi.examples-complete.php#ffi.examples-complete for details
     *
     * @return $this
     */
    public function initializePreloaded(): static
    {
        if ($this->initialized) {
            return $this;
        }

        $this->ffi = FFI::scope(static::DEFAULT_FFI_SCOPE);
        $this->initialized = true;
        return $this;
    }

    /**
     * Get a new Rados cluster object
     *
     * @return Cluster
     * @throws RadosException
     */
    public function getCluster(): Cluster
    {
        if (!$this->initialized) {
            throw new RadosException("Rados is not initialized");
        }
        return Cluster::create($this->ffi);
    }
}
