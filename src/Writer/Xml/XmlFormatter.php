<?php
namespace DemandwareXml\Writer\Xml;

use DateTimeInterface;

// Converts values into strings suitable for XML output.
class XmlFormatter
{
    public const DATE_FORMAT_DEFAULT = 'Y-m-d\TH:i:s';

    /**
     * Sanitise a string for XML.
     *
     * @see   http://www.phpwact.org/php/i18n/charsets#common_problem_areas_with_utf-8
     * @see   http://www.xiven.com/weblog/2013/08/30/PHPInvalidUTF8InXMLRevisited
     */
    public static function sanitise(?string $string): string
    {
        // Only allow Tab (9), LF (10), CR (13), Space (32) - 55295, 57344 - 65533, 65536 - 1114111.
        return preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', ' ', $string);
    }

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

    // Checks whether a value is null, an empty array, or an empty string.
    public static function isEmptyValue($value): bool
    {
        if ($value === null || $value === []) {
            return true;
        }

        return (bool) (is_string($value) && $value === '');
    }

    // Filters out empty values from an array.
    public static function filterEmptyValues(array $values): array
    {
        return array_filter($values, fn ($value) => self::isEmptyValue($value) === false);
    }
}
