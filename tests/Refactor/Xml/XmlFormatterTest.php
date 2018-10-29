<?php
namespace DemandwareXml\Test\Refactor\Xml;

use DateTime;
use DateTimeImmutable;
use DemandwareXml\Refactor\Xml\XmlFormatter;
use DemandwareXml\Refactor\Xml\XmlFormatterException;
use PHPUnit\Framework\TestCase;
use stdClass;

class XmlFormatterTest extends TestCase
{
    public function test_sanitise(): void
    {
        $invalidChar = chr(30); // Record Separator.

        $this->assertSame('Foo Bar', XmlFormatter::sanitise('Foo' . $invalidChar . 'Bar'));
    }

    /**
     * @dataProvider from_boolean_data_provider
     */
    public function test_from_boolean($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::fromBoolean($value));
    }

    public function from_boolean_data_provider(): array
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
     * @dataProvider from_datetime_data_provider
     */
    public function test_from_datetime($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::fromDateTime($value));
    }

    public function from_datetime_data_provider(): array
    {
        return [
            'null value'                => [null, ''],
            'datetime object'           => [new Datetime('2001-02-03 04:04:06'), '2001-02-03T04:04:06'],
            'immutable datetime object' => [new DateTimeImmutable('2001-02-03 04:04:06'), '2001-02-03T04:04:06'],
        ];
    }

    /**
     * @dataProvider from_type_data_provider
     */
    public function test_from_type($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::fromType($value));
    }

    public function from_type_data_provider(): array
    {
        $toStringClass = new class {
            public function __toString()
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
     * @dataProvider from_type_exception_data_provider
     */
    public function test_from_type_exception($value, $expectedExceptionClass, $expectedExceptionMessage): void
    {
        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        XmlFormatter::fromType($value);
    }

    public function from_type_exception_data_provider(): array
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
     * @dataProvider is_empty_value_data_provider
     */
    public function test_is_empty_value($value, $expectedResult): void
    {
        $this->assertSame($expectedResult, XmlFormatter::isEmptyValue($value));
    }

    public function is_empty_value_data_provider(): array
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
        $this->assertEquals(
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
        $this->assertEquals(
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
