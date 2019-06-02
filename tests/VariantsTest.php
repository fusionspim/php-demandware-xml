<?php
namespace DemandwareXml\Test;

use DemandwareXml\{Document, Variant};
use PHPUnit\Framework\TestCase;

class VariantsTest extends TestCase
{
    use FixtureHelper;

    public function test_variants_xml(): void
    {
        $document = new Document('TestCatalog');

        $variants = [
            'colour' => ['red' => 'Red', 'blue' => 'Blue'],
            'height' => ['H1' => 'Grande', 'H2' => 'Tall'],
        ];

        foreach ($variants as $variant => $values) {
            $element = new Variant($variant);
            $element->addTags($values);
            $document->addObject($element);
        }

        $sampleXml = $this->loadFixture('variants.xml');
        $outputXml = $document->getDomDocument();

        $this->assertEqualXMLStructure($sampleXml->firstChild, $outputXml->firstChild);
    }
}
