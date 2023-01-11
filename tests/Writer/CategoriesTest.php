<?php

namespace DemandwareXml\Test\Writer;

use DateTimeImmutable;
use DemandwareXml\Test\FixtureHelper;
use DemandwareXml\Writer\Entity\Assignment;
use DemandwareXml\Writer\Entity\Category;
use DemandwareXml\Writer\Entity\DeletedAssignment;
use DemandwareXml\Writer\Entity\DeletedCategory;
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

        $categories = [
            'Socks'       => [
                'index' => 0,
                'dates' => [
                    'from' => new DateTimeImmutable('2018-01-01 01:01:01'),
                    'to'   => new DateTimeImmutable('2018-02-02 02:02:02'),
                ],
            ],
            'Death Stars' => [
                'index' => 1,
                'dates' => [
                    'from' => null,
                    'to'   => new DateTimeImmutable('2018-02-02 02:02:02'),
                ],
            ],
            'Donuts'      => [
                'index' => 2,
                'dates' => [
                    'from' => new DateTimeImmutable('2018-01-01 01:01:01'),
                    'to'   => null,
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
                $data['dates']['from'],
                $data['dates']['to']
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
