<?php

namespace Aternos\Rados\Util;

use FFI;
use InvalidArgumentException;

class StringArray extends WrappedType
{
    /**
     * @param string[] $array
     * @param FFI $ffi
     */
    public function __construct(array $array, FFI $ffi)
    {
        $array = array_values($array);
        foreach ($array as $elem) {
            if (!is_string($elem)) {
                throw new InvalidArgumentException("All elements of the array must be strings");
            }
        }

        $result = $ffi->new(FFI::arrayType($ffi->type("char*"), [count($array)]));
        foreach ($array as $i => $elem) {
            $type = FFI::arrayType($ffi->type("char"), [strlen($elem) + 1]);
            $value = $ffi->new($type, false);
            FFI::memcpy($value, $elem . "\0", strlen($elem) + 1);
            $result[$i] = $value;
        }

        parent::__construct($result, $ffi);
    }

    /**
     * @inheritDoc
     */
    protected function releaseCData(): void
    {
        foreach ($this->getCData() as $value) {
            FFI::free($value);
        }
    }
}
