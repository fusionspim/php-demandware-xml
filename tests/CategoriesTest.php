<?php
namespace DemandwareXml\Test;

use \DemandwareXml\Assignment;
use \DemandwareXml\Category;
use \DemandwareXml\Document;

class CategoriesTest extends AbstractTest
{
    public function testCategoriesXml()
    {
        $document = new Document('TestCatalog');

        foreach (['Socks', 'Death Stars', 'Donuts'] as $index => $example) {
            $element = new Category('CAT' . $index);
            $element->setName($example);
            $element->setParent('CAT0');
            $element->setTemplate('cat-listings.html');
            $element->setFlags(true);
            $element->setSitemap(0.2);
            $element->setPageAttributes($example, 'Buy ' . $example, strtolower($example), '/' . $example);
            $element->setCustomAttributes([
                'itemsPerPage' => 30,
                'promoMast'    => 'cat' . $index . '-banner.png',
                'hasOffers'    => true
            ]);
            $document->addObject($element);
        }

        $sampleXml = $this->loadFixture('categories.xml');
        $outputXml = $document->getDomDocument();

        $this->assertEqualXMLStructure($sampleXml->firstChild, $outputXml->firstChild);
    }

    public function testAssignmentsXml()
    {
        $document = new Document('TestCatalog');

        // PROD1 has a primary assignment, non-primary, and unspecified
        $element = new Assignment('PROD1', 'CAT1');
        $element->setPrimary(false);
        $document->addObject($element);

        $element = new Assignment('PROD1', 'CAT2');
        $element->setPrimary(true);
        $document->addObject($element);

        $element = new Assignment('PROD1', 'CAT42');
        $document->addObject($element);

        // PROD2 has a deleted assignment
        $element = new Assignment('PROD2', 'CAT1');
        $element->setDeleted();
        $document->addObject($element);

        // PROD3 has a primary assignment
        $element = new Assignment('PROD3', 'CAT3');
        $element->setPrimary(false);
        $document->addObject($element);

        $sampleXml = $this->loadFixture('assignments.xml');
        $outputXml = $document->getDomDocument();

        $this->assertEqualXMLStructure($sampleXml->firstChild, $outputXml->firstChild);
    }
}
