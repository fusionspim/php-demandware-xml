<?php
namespace DemandwareXml\Refactor\Helper;

use DateTime;

class Formatter
{
    const DATE_FORMAT_DEFAULT = 'Y-m-d\TH:i:s';

    public static function isEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && mb_strlen($value) === 0) {
            return true;
        }

        return false;
    }

    public static function filterEmpty(array $values): array
    {
        return array_filter($values, function ($value) {
            return self::isEmpty($value) === false;
        });
    }

    public static function fromType($value): string
    {
        switch (gettype($value)) {
            case 'boolean':
                return self::asBoolean($value);

            case 'object':
                if ($value instanceof DateTime) {
                    return self::asDateTime($value);
                }
            default:
                return (string) $value;
        }
    }

    public static function asBoolean($value): string
    {
        if ($value === null) {
            return '';
        }

        return ($value ? 'true' : 'false');
    }

    public static function asDateTime($value, string $format = self::DATE_FORMAT_DEFAULT): string
    {
        if ($value === null || (is_string($value) && mb_strlen(trim($value)) === 0)) {
            return '';
        }

        if (is_string($value)) {
            $value = new DateTime($value);
        }

        return $value->format($format);
    }
}
