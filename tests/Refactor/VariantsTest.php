<?php
namespace DemandwareXml\Test\Refactor;

use DemandwareXml\Refactor\Entity\Variant;
use DemandwareXml\Refactor\Xml\XmlWriter;
use DemandwareXml\Test\FixtureHelper;
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
