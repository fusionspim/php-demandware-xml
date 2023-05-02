<?php

namespace DemandwareXml\Test\Writer\Xml;

use DateTime;
use DateTimeImmutable;
use DemandwareXml\Writer\Xml\XmlFormatter;
use DemandwareXml\Writer\Xml\XmlFormatterException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;

final class XmlFormatterTest extends TestCase
{
    /**
     * @param  mixed  $value
     * @param  mixed  $expectedResult
     */
    #[DataProvider('sanitise_data_provider')]
    public function test_sanitise($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::sanitise($value));
    }

    public static function sanitise_data_provider(): iterable
    {
        return [
            'null'             => [null, ''],
            'record separator' => ['Foo' . chr(30) . 'Bar', 'Foo Bar'],
            'start of text'    => ['Foo' . chr(2) . 'Bar', 'Foo Bar'],
        ];
    }

    /**
     * @param  mixed  $value
     * @param  mixed  $expectedResult
     */
    #[DataProvider('from_boolean_data_provider')]
    public function test_from_boolean($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::fromBoolean($value));
    }

    public static function from_boolean_data_provider(): iterable
    {
        return [
            'null value'    => [null, ''],
            'true boolean'  => [true, 'true'],
            'false boolean' => [false, 'false'],
            'truthy value'  => ['foobar', 'true'],
            'falsey value'  => ['', 'false'],
        ];
    }

    /**
     * @param  mixed  $value
     * @param  mixed  $expectedResult
     */
    #[DataProvider('from_datetime_data_provider')]
    public function test_from_datetime($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::fromDateTime($value));
    }

    public static function from_datetime_data_provider(): iterable
    {
        return [
            'null value'                => [null, ''],
            'datetime object'           => [new Datetime('2001-02-03 04:04:06'), '2001-02-03T04:04:06'],
            'immutable datetime object' => [new DateTimeImmutable('2001-02-03 04:04:06'), '2001-02-03T04:04:06'],
        ];
    }

    /**
     * @param  mixed  $value
     * @param  mixed  $expectedResult
     */
    #[DataProvider('from_type_data_provider')]
    public function test_from_type($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::fromType($value));
    }

    public static function from_type_data_provider(): iterable
    {
        $toStringClass = new class implements Stringable
        {
            public function __toString(): string
            {
                return 'TOSTRING';
            }
        };

        return [
            'null value'                => [null, ''],
            'string value'              => ['FOOBAR', 'FOOBAR'],
            'int value'                 => [42, '42'],
            'float value'               => [42.42, '42.42'],
            'true boolean'              => [true, 'true'],
            'false boolean'             => [false, 'false'],
            'truthy value'              => ['foobar', 'foobar'],
            'falsey value'              => ['', ''],
            'datetime object'           => [new Datetime('2001-02-03 04:04:06'), '2001-02-03T04:04:06'],
            'immutable datetime object' => [new DateTimeImmutable('2001-02-03 04:04:06'), '2001-02-03T04:04:06'],
            'object with __toString'    => [new $toStringClass, 'TOSTRING'],
        ];
    }

    /**
     * @param  mixed  $value
     * @param  mixed  $expectedExceptionClass
     * @param  mixed  $expectedExceptionMessage
     */
    #[DataProvider('from_type_exception_data_provider')]
    public function test_from_type_exception($value, $expectedExceptionClass, $expectedExceptionMessage): void
    {
        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        XmlFormatter::fromType($value);
    }

    public static function from_type_exception_data_provider(): iterable
    {
        return [
            'array value' => [
                ['foo' => 'bar'],
                XmlFormatterException::class,
                'Cannot convert array to a string',
            ],

            'object without __toString' => [
                new stdClass,
                XmlFormatterException::class,
                'Cannot convert object without __toString() method to a string',
            ],
        ];
    }

    /**
     * @param  mixed  $value
     * @param  mixed  $expectedResult
     */
    #[DataProvider('is_empty_value_data_provider')]
    public function test_is_empty_value($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::isEmptyValue($value));
    }

    public static function is_empty_value_data_provider(): iterable
    {
        return [
            'null value'         => [null, true],
            'empty string value' => ['', true],
            'empty array value'  => [[], true],
            'string value'       => ['FOOBAR', false],
            'int value'          => [42, false],
            'float value'        => [42.42, false],
            'true boolean'       => [true, false],
            'false boolean'      => [false, false],
            'array value'        => [['foo' => 'bar'], false],
            'object'             => [new stdClass, false],
        ];
    }

    public function test_filter_empty_values_with_keys(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                'string' => 'FOOBAR',
                'int'    => 42,
                'float'  => 42.42,
                'true'   => true,
                'false'  => false,
                'array'  => ['foo' => 'bar'],
                'object' => new stdClass,
            ],
            XmlFormatter::filterEmptyValues([
                'null'         => null,
                'empty-string' => '',
                'empty-array'  => [],
                'string'       => 'FOOBAR',
                'int'          => 42,
                'float'        => 42.42,
                'true'         => true,
                'false'        => false,
                'array'        => ['foo' => 'bar'],
                'object'       => new stdClass,
            ])
        );
    }

    public function test_filter_empty_values_without_keys(): void
    {
        $this->assertEqualsCanonicalizing(
            array_values([
                'FOOBAR',
                42,
                42.42,
                true,
                false,
                ['foo' => 'bar'],
                new stdClass,
            ]),
            array_values(
                XmlFormatter::filterEmptyValues([
                    'null'         => null,
                    'empty-string' => '',
                    'empty-array'  => [],
                    'string'       => 'FOOBAR',
                    'int'          => 42,
                    'float'        => 42.42,
                    'true'         => true,
                    'false'        => false,
                    'array'        => ['foo' => 'bar'],
                    'object'       => new stdClass,
                ])
            )
        );
    }
}
