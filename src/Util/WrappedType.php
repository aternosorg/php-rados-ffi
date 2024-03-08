<?php

namespace Aternos\Rados\Util;

use FFI;
use FFI\CData;

class WrappedType
{
    protected CData $data;
    protected FFI $ffi;

    /**
     * @param FFI $ffi
     * @param array $array
     * @return CData
     */
    protected static function createStringArray(FFI $ffi, array $array): CData
    {
        $result = $ffi->new(FFI::arrayType($ffi->type("char*"), [count($array)]));
        foreach ($array as $i => $elem) {
            $type = FFI::arrayType($ffi->type("char"), [strlen($elem) + 1]);
            $value = $ffi->new($type, false);
            FFI::memcpy($value, $elem . "\0", strlen($elem) + 1);
            $result[$i] = $value;
        }
        return $result;
    }

    /**
     * @param CData $array
     * @return void
     */
    protected static function freeStringArray(CData $array): void
    {
        foreach ($array as $value) {
            FFI::free($value);
        }
    }

    /**
     * @param Buffer $buffer
     * @param int $length
     * @return string[]
     */
    protected static function parseNullTerminatedStringList(Buffer $buffer, int $length): array
    {
        $parts = explode("\0", $buffer->readString($length));
        $result = [];
        foreach ($parts as $part) {
            if ($part === "") {
                break;
            }
            $result[] = $part;
        }
        return $result;
    }

    /**
     * @param CData $data
     * @param FFI $ffi
     */
    public function __construct(CData $data, FFI $ffi)
    {
        $this->data = $data;
        $this->ffi = $ffi;
    }

    /**
     * @return CData
     */
    public function getCData(): CData
    {
        return $this->data;
    }

    /**
     * @return FFI
     */
    public function getFFI(): FFI
    {
        return $this->ffi;
    }
}
