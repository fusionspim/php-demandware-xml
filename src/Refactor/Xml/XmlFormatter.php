<?php
namespace DemandwareXml\Refactor\Xml;

use DateTimeInterface;

// Converts values into strings suitable for XML output.
class XmlFormatter
{
    const DATE_FORMAT_DEFAULT = 'Y-m-d\TH:i:s';

    // Convert a boolean into a string for use in XML output.
    public static function fromBoolean(?bool $value): string
    {
        if ($value === null) {
            return '';
        }

        return ($value ? 'true' : 'false');
    }

    // Convert a DateTime object into a string for use in XML output.
    public static function fromDateTime(?DateTimeInterface $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value->format(self::DATE_FORMAT_DEFAULT);
    }

    // Convert value into a string for use in XML output from its type.
    public static function fromType($value): string
    {
        switch (gettype($value)) {
            case 'boolean':
                return self::fromBoolean($value);

            case 'object':
                if ($value instanceof DateTimeInterface) {
                    return self::fromDateTime($value);
                }

                if (method_exists($value, '__toString')) {
                    return (string) $value;
                }

                throw new XmlFormatterException('Cannot convert object without __toString() method to a string');

            case 'array':
                throw new XmlFormatterException('Cannot convert array to a string');

            default:
                return (string) $value;
        }
    }

    // Checks whether a value is null, an empty array, or an empty multi-byte string.
    public static function isEmptyValue($value): bool
    {
        if ($value === null || $value === []) {
            return true;
        }

        if (is_string($value) && mb_strlen($value) === 0) {
            return true;
        }

        return false;
    }

    // Filters out empty values from an array.
    public static function filterEmptyValues(array $values): array
    {
        return array_filter($values, function ($value) {
            return self::isEmptyValue($value) === false;
        });
    }
}
