<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;
use Aternos\Rados\Util\StringArray;
use FFI;

/**
 * Set key/value pairs on an object
 *
 * @extends WriteOperationTask<null>
 */
class OMapSetTask extends WriteOperationTask
{
    /**
     * @param string[] $values - associative array of key-value pairs
     */
    public function __construct(
        protected array $values
    )
    {
    }

    /**
     * @inheritDoc
     */
    protected function parseResult(): null
    {
        return null;
    }

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function initTask(Operation $operation): void
    {
        $ffi = $operation->getFFI();
        $count = count($this->values);
        $keyLengths = $ffi->new(FFI::arrayType($ffi->type('size_t'), [$count]));
        $valueLengths = $ffi->new(FFI::arrayType($ffi->type('size_t'), [$count]));
        $keyEntries = [];
        $valueEntries = [];
        $i = 0;
        foreach ($this->values as $key => $value) {
            $keyEntries[$i] = $key;
            $valueEntries[$i] = $value;
            $keyLengths[$i] = strlen($key);
            $valueLengths[$i] = strlen($value);
            $i++;
        }

        $keys = new StringArray($keyEntries, $operation->getFFI());
        $values = new StringArray($valueEntries, $operation->getFFI());

        $operation->getFFI()->rados_write_op_omap_set2(
            $operation->getCData(),
            $keys->getCData(),
            $values->getCData(),
            $keyLengths,
            $valueLengths,
            $count
        );
    }
}
