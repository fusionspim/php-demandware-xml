<?php
namespace DemandwareXml\Test;

use \DemandwareXml\Test\AbstractTest;
use \DemandwareXml\Assignment;
use \DemandwareXml\Category;
use \DemandwareXml\Document;
use \DemandwareXml\XmlException;

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

        foreach (['PROD1' => 'CAT1', 'PROD1' => 'CAT2', 'PROD2' => 'CAT1', 'PROD3' => 'CAT3'] as $product => $category) {
            // simulate some application logic
            $primary = ('PROD1' === $product && 'CAT2' === $category);
            $deleted = ('PROD2' === $product && 'CAT1' === $category);
            $element = new Assignment($product, $category);
            // flag as deleted if app logic says so, otherwise handle primary flag...
            if ($deleted) {
                $element->setDeleted();
            } else {
                $element->setPrimary($primary);
            }
            $document->addObject($element);
            // simulate some more application logic, put all primary products in CAT42
            if ($primary) {
                $element = new Assignment($product, 'CAT42');
                $document->addObject($element);
            }
        }

        $sampleXml = $this->loadFixture('assignments.xml');
        $outputXml = $document->getDomDocument();

        $this->assertEqualXMLStructure($sampleXml->firstChild, $outputXml->firstChild);
    }
}
