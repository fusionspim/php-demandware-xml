<?php
namespace DemandwareXml\Test;

use DemandwareXml\Xml;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @dataProvider escapeDataProvider
     */
    public function testEscape($unescaped, string $escaped): void
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

    public function testSanitise(): void
    {
        $invalidChar = chr(30); // Record Separator.

        $this->assertSame('Foo Bar', Xml::sanitise('Foo' . $invalidChar . 'Bar'));
    }

    public function testValidateXml(): void
    {
        $xmlPath = __DIR__ . '/fixtures/products.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }

    /**
     * @expectedException              \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp /xmlParseEntityRef: no name/
     */
    public function testValidateInvalidXml(): void
    {
        $xmlPath = __DIR__ . '/fixtures/invalid-products.xml';

        $this->assertFalse(Xml::validate($xmlPath));
    }

    public function testValidateTaxonomySample(): void
    {
        $xmlPath = __DIR__ . '/fixtures/taxonomy-sample.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }
}
