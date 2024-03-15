<?php

namespace Aternos\Rados\Operation\Write\Task;

use Aternos\Rados\Operation\Operation;
use Aternos\Rados\Operation\Write\WriteOperationTask;
use Aternos\Rados\Util\StringArray;
use FFI;

/**
 * Remove key/value pairs from an object
 *
 * @extends WriteOperationTask<null>
 */
class OMapRemoveKeysTask extends WriteOperationTask
{
    /**
     * @param string[] $keys
     */
    public function __construct(
        protected array $keys
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

        $operation->getFFI()->rados_write_op_omap_rm_keys2(
            $operation->getCData(),
            $keys->getCData(),
            $keyLengths,
            $count
        );
    }
}
