<?php
namespace DemandwareXml\Test;

use DemandwareXml\{Xml, XmlException};
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @dataProvider escapeDataProvider
     */
    public function testEscape($unescaped, string $escaped)
    {
        $this->assertEquals($escaped, Xml::escape($unescaped));
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

    public function testSanitise()
    {
        $invalidChar = chr(30); // Record Separator.

        $this->assertEquals('Foo Bar', Xml::sanitise('Foo' . $invalidChar . 'Bar'));
    }

    public function testValidateXml()
    {
        $xmlPath = __DIR__ . '/fixtures/products.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }

    /**
     * @expectedException              \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp /xmlParseEntityRef: no name/
     */
    public function testValidateInvalidXml()
    {
        $xmlPath = __DIR__ . '/fixtures/invalid-products.xml';

        $this->assertFalse(Xml::validate($xmlPath));
    }

    public function testValidateTaxonomySample()
    {
        $xmlPath = __DIR__ . '/fixtures/taxonomy-sample.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }
}
