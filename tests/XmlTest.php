<?php
namespace DemandwareXml\Test;

use DemandwareXml\Xml;
use DemandwareXml\XmlException;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
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
