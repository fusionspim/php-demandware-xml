<?php
namespace DemandwareXml\Test\Writer;

use DateTimeImmutable;
use DemandwareXml\Test\FixtureHelper;
use DemandwareXml\Writer\Entity\{Assignment, Category, DeletedAssignment, DeletedCategory};
use DemandwareXml\Writer\Xml\XmlWriter;
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
        $xml->writeAttributeNS('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');
        
        $categories = [
            'Socks' => [
                'index' => 0,
                'dates' => [
                    new DateTimeImmutable('2018-01-01 01:01:01'),
                    new DateTimeImmutable('2018-02-02 02:02:02'),
                ],
            ],
            'Death Stars' => [
                'index' => 1,
                'dates' => [
                    null,
                    new DateTimeImmutable('2018-02-02 02:02:02'),
                ],
            ],
            'Donuts' => [
                'index' => 2,
                'dates' => [
                    new DateTimeImmutable('2018-01-01 01:01:01'),
                    null,
                ],
            ],
        ];

        foreach ($categories as $title => $data) {
            $element = new Category('CAT' . $data['index']);
            $element->setDisplayName($title);
            $element->setParent('CAT0');
            $element->setTemplate('cat-listings.html');
            $element->setOnlineFlag(true);
            $element->setSitemap(0.2);
            $element->setPageAttributes($title, 'Buy ' . $title, mb_strtolower($title), '/' . $title);
            $element->setOnlineFromTo(
                $data['dates'][0],
                $data['dates'][1]
            );

            $element->addCustomAttributes([
                'itemsPerPage' => 30,
                'promoMast'    => 'cat' . $data['index'] . '-banner.png',
                'hasOffers'    => true,
            ]);

            $xml->writeEntity($element);
        }

        $xml->endCatalog();
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

        $xml->endCatalog();
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
        $xml->endCatalog();
        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('category-deleted.xml'),
            $xml->outputMemory(true)
        );
    }
}
