<?php
namespace DemandwareXml\Test;

use \PHPUnit_Framework_TestCase;
use \DemandwareXml\Xml;
use \DemandwareXml\XmlException;

class XmlTest extends PHPUnit_Framework_TestCase
{
    public function testValidateXml()
    {
        $xmlPath = __DIR__ . '/fixtures/products.xml';

        $this->assertTrue(Xml::validate($xmlPath));
    }

    /**
     * @expectedException       \DemandwareXml\XmlException
     * @expectedExceptionRegExp /xmlParseEntityRef: no name/
     */
    public function testValidateInvalidXml()
    {
        $xmlPath = __DIR__ . '/fixtures/invalid_items.xml';

        $this->assertFalse(Xml::validate($xmlPath));
    }
}
