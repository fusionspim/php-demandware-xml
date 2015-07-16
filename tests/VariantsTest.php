<?php
namespace FusionsPIM\DemandwareXml\Test;

use \DemandwareXml\Test\AbstractTest;
use \DemandwareXml\Document;
use \DemandwareXml\Variant;
use \DemandwareXml\XmlException;

class VariantsTest extends AbstractTest
{
    public function testVariantsXml()
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
