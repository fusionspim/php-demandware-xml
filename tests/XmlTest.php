<?php
namespace DemandwareXml\Test;

use DemandwareXml\{Xml, XmlException};
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @dataProvider escapeDataProvider
     */
    public function test_escape($unescaped, string $escaped): void
    {
        $this->assertSame($escaped, Xml::escape($unescaped));
    }

    public function escapeDataProvider()
    {
        return [
            [true, 'true'],
            [false, 'false'],
            [
                '<el>"Double Quotes" & \'Single Quotes\'</el>',
                '&lt;el&gt;&quot;Double Quotes&quot; &amp; &apos;Single Quotes&apos;&lt;/el&gt;',
            ],
        ];
    }

    public function test_sanitise(): void
    {
        $invalidChar = chr(30); // Record Separator.

        $this->assertSame('Foo Bar', Xml::sanitise('Foo' . $invalidChar . 'Bar'));
    }

    public function test_validate_xml(): void
    {
        $xmlPath = __DIR__ . '/fixtures/products.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }

    public function test_validate_invalid_xml(): void
    {
        $this->expectException(XmlException::class);
        $this->expectExceptionMessageRegExp('/xmlParseEntityRef: no name/');

        $xmlPath = __DIR__ . '/fixtures/invalid-products.xml';

        $this->assertFalse(Xml::validate($xmlPath));
    }

    public function test_validate_taxonomy_sample(): void
    {
        $xmlPath = __DIR__ . '/fixtures/taxonomy-sample.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }
}
