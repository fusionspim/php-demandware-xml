<?php
namespace DemandwareXml\Test\Refactor;

use DateTimeImmutable;
use DemandwareXml\Refactor\Entity\{Assignment, Category, CustomAttribute, DeletedAssignment, DeletedCategory};
use DemandwareXml\Refactor\Xml\XmlWriter;
use DemandwareXml\Test\FixtureHelper;
use PHPUnit\Framework\TestCase;

class CategoriesTest extends TestCase
{
    use FixtureHelper;

    public function test_categories_xml(): void
    {
        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');

        foreach (['Socks', 'Death Stars', 'Donuts'] as $index => $example) {
            $element = new Category('CAT' . $index);
            $element->setDisplayName($example);
            $element->setParent('CAT0');
            $element->setTemplate('cat-listings.html');
            $element->setOnlineFlag(true);
            $element->setSitemap(0.2);
            $element->setPageAttributes($example, 'Buy ' . $example, mb_strtolower($example), '/' . $example);
            $element->setOnlineFromTo(
                new DateTimeImmutable('2018-01-01 01:01:01'),
                new DateTimeImmutable('2018-02-02 02:02:02')
            );

            $customAttributes = [
                'itemsPerPage' => 30,
                'promoMast'    => 'cat' . $index . '-banner.png',
                'hasOffers'    => true,
            ];

            foreach ($customAttributes as $id => $value) {
                $element->addCustomAttribute(new CustomAttribute($id, $value));
            }

            $xml->writeEntity($element);
        }

        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('categories.xml'),
            $xml->outputMemory(true)
        );
    }

    public function test_assignments_xml(): void
    {
        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');

        // PROD1 has a primary assignment, non-primary, and unspecified
        $element = new Assignment('PROD1', 'CAT1');
        $element->setPrimary(false);
        $xml->writeEntity($element);

        $element = new Assignment('PROD1', 'CAT2');
        $element->setPrimary(true);
        $xml->writeEntity($element);

        $xml->writeEntity(new Assignment('PROD1', 'CAT42'));

        // PROD2 has a deleted assignment
        $xml->writeEntity(new DeletedAssignment('PROD2', 'CAT1'));

        // PROD3 has a non-primary assignment (despite what the old comment said)
        $element = new Assignment('PROD3', 'CAT3');
        $element->setPrimary(false);
        $xml->writeEntity($element);

        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('assignments.xml'),
            $xml->outputMemory(true)
        );
    }

    public function test_categories_deleted_xml(): void
    {
        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');
        $xml->writeEntity(new DeletedCategory('CAT123'));
        $xml->writeEntity(new DeletedCategory('CAT456'));
        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('category-deleted.xml'),
            $xml->outputMemory(true)
        );
    }
}
