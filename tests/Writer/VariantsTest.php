<?php

namespace DemandwareXml\Test\Writer;

use DemandwareXml\Test\FixtureHelper;
use DemandwareXml\Writer\Entity\Variant;
use DemandwareXml\Writer\Xml\XmlWriter;
use PHPUnit\Framework\TestCase;

class VariantsTest extends TestCase
{
    use FixtureHelper;

    public function test_variants_xml(): void
    {
        $variants = [
            'colour' => ['red' => 'Red', 'blue' => 'Blue'],
            'height' => ['H1' => 'Grande', 'H2' => 'Tall'],
        ];

        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');

        foreach ($variants as $variant => $values) {
            $element = new Variant($variant);

            foreach ($values as $value => $displayValue) {
                $element->addDisplayValue($value, $displayValue);
            }

            $xml->writeEntity($element);
        }

        $xml->endCatalog();
        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('variants.xml'),
            $xml->outputMemory(true)
        );
    }
}
