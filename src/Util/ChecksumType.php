<?php

namespace Aternos\Rados\Util;

use Aternos\Rados\Exception\RadosException;
use FFI;

enum ChecksumType: string
{
    case XXHASH32 = "LIBRADOS_CHECKSUM_TYPE_XXHASH32";
    case XXHASH64 = "LIBRADOS_CHECKSUM_TYPE_XXHASH64";
    case CRC32C = "LIBRADOS_CHECKSUM_TYPE_CRC32C";

    /**
     * Get the length of the checksum in bytes
     *
     * @return int
     */
    public function getLength(): int
    {
        return match ($this) {
            self::XXHASH32, self::CRC32C => 4,
            self::XXHASH64 => 8,
        };
    }

    /**
     * Get the pack() format code for the checksum
     *
     * @return string
     */
    public function getPackFormat(): string
    {
        return match ($this) {
            self::XXHASH32, self::CRC32C => "V",
            self::XXHASH64 => "P",
        };
    }

    /**
     * @param FFI $ffi
     * @return int
     */
    public function getCValue(FFI $ffi): int
    {
        return $ffi->{$this->value};
    }

    /**
     * @param int $initValue
     * @return string
     */
    public function createInitString(int $initValue): string
    {
        return pack($this->getPackFormat(), $initValue);
    }

    /**
     * @param string $packed
     * @return array
     * @throws RadosException
     */
    public function unpack(string $packed): array
    {
        $actualResultCount = unpack("V", $packed)[1];
        $results = @unpack($this->getPackFormat() . $actualResultCount, $packed, 4);
        if ($results === false) {
            throw new RadosException("Failed to unpack checksum result");
        }

        return array_values($results);
    }
}
