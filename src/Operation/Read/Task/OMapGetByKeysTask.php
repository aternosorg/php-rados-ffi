<?php

namespace Aternos\Rados\Operation\Read\Task;

use Aternos\Rados\Cluster\Pool\Object\OMap\OMapIterator;
use Aternos\Rados\Cluster\Pool\Object\OMap\OMapReadResult;
use Aternos\Rados\Exception\RadosException;
use Aternos\Rados\Exception\RadosObjectException;
use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Read\ReadOperationTask;
use Aternos\Rados\Util\StringArray;
use FFI;
use FFI\CData;
use InvalidArgumentException;
use RuntimeException;

/**
 * Start iterating over specific key/value pairs
 *
 * @extends ReadOperationTask<OMapReadResult>
 */
class OMapGetByKeysTask extends ReadOperationTask
{
    protected ?CData $result = null;
    protected ?CData $iterator = null;

    /**
     * @param string[] $keys
     */
    public function __construct(
        protected array $keys
    )
    {
        foreach ($this->keys as $key) {
            if (!is_string($key)) {
                throw new InvalidArgumentException("All elements of the array must be strings");
            }
        }
    }

    /**
     * @inheritDoc
     * @throws RadosException
     */
    protected function parseResult(): OMapReadResult
    {
        if ($this->result === null) {
            throw new RuntimeException("No result available");
        }
        RadosObjectException::handle($this->result->cdata);

        return new OMapReadResult(
            new OMapIterator($this->iterator, $this->operation->getFFI()),
            false
        );
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $ffi = $operation->getFFI();
        $this->result = $ffi->new('int');
        $this->iterator = $ffi->new('rados_omap_iter_t');

        $count = count($this->keys);
        $keyLengths = $ffi->new(FFI::arrayType($ffi->type('size_t'), [$count]));
        $keyEntries = [];
        $i = 0;
        foreach ($this->keys as $key) {
            $keyEntries[$i] = $key;
            $keyLengths[$i] = strlen($key);
            $i++;
        }

        $keys = new StringArray($keyEntries, $operation->getFFI());

        $operation->getFFI()->rados_read_op_omap_get_vals_by_keys2(
            $operation->getCData(),
            $keys->getCData(),
            $count,
            $keyLengths,
            FFI::addr($this->iterator),
            FFI::addr($this->result)
        );
    }
}
