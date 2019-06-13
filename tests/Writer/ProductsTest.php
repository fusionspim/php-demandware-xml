<?php
namespace DemandwareXml\Test\Writer;

use DateTimeImmutable;
use DemandwareXml\Test\FixtureHelper;
use DemandwareXml\Writer\Entity\{CustomAttribute, DeletedProduct, Product};
use DemandwareXml\Writer\Xml\XmlWriter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    use FixtureHelper;

    public function test_products_xml(): void
    {
        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('products.xml'),
            $this->buildDocument()->outputMemory(true)
        );
    }

    public function test_products_deleted_xml(): void
    {
        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');
        $xml->writeEntity(new DeletedProduct('PRODUCT123'));
        $xml->writeEntity(new DeletedProduct('VARIATION123'));
        $xml->endCatalog();
        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('products-deleted.xml'),
            $xml->outputMemory(true)
        );
    }

    public function test_xml_escaping(): void
    {
        $entity = $this->buildProductElement();
        $entity->setDisplayName('Product number 123 &bull;');
        $entity->addCustomAttribute(new CustomAttribute('foobar', '&bull;'));

        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');
        $xml->writeEntity($entity);
        $xml->endCatalog();
        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('product-xml-escaped.xml'),
            $xml->outputMemory(true)
        );
    }

    public function test_invalid_sitemap_priority(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sitemap priority must be 1.0 or less');

        $entity = $this->buildProductElement();
        $entity->setSitemap(42.5);
    }

    protected function buildDocument(): XmlWriter
    {
        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');
        $xml->writeEntity($this->buildProductElement());
        $xml->writeEntity($this->buildSetElement());
        $xml->writeEntity($this->buildBundleElement());
        $xml->writeEntity($this->buildVariationElement());
        $xml->endCatalog();
        $xml->endDocument();

        return $xml;
    }

    protected function buildBaseElement(string $type, int $number = 0): Product
    {
        $invalidChar = chr(30); // Record Separator.

        $element = new Product(mb_strtoupper($type) . '123');
        $element->setDisplayName($type . ' number 123');
        $element->setLongDescription('<b>' . $type . '</b> The description for an <i>example</i> ' . mb_strtolower($type) . '! • Bullet' . $invalidChar . 'Point');
        $element->setUpc('50000000000' . $number);
        $element->setQuantities(); // include, but use defaults
        $element->setSearchRank(1);
        $element->setBrand('SampleBrand™');
        $element->setOnlineFlag(true);
        $element->setSearchableFlags(null, false, null);

        $element->setOnlineFromTo(
            new DateTimeImmutable('2015-01-23 01:23:45'),
            new DateTimeImmutable('2025-01-23 01:23:45')
        );

        $element->setPageAttributes(
            'Amazing ' . $type,
            'Buy our ' . $type . ' today!',
            $type . ', test, example',
            'http://example.com/' . mb_strtolower($type) . '/123'
        );

        $customAttributes = [
            'type'         => 'Examples',
            'zzz'          => 'Should be exported last within custom-attributes',
            'primaryImage' => mb_strtolower($type) . '-123.png',
            'multiWow'     => ['so', 'such', 'many', 'much', 'very'],
            'boolTrue'     => true,
            'boolFalse'    => false,
        ];

        foreach ($customAttributes as $id => $value) {
            $element->addCustomAttribute(new CustomAttribute($id, $value));
        }

        $element->setImages([mb_strtolower($type) . '-123.png']);

        return $element;
    }

    protected function buildProductElement(): Product
    {
        $element = $this->buildBaseElement('Product');
        $element->setClassificationCategory('CAT123', 'TestCatalog');
        $element->setSitemap(1.0);
        $element->setImages(['product-123.png']);
        $element->setSharedVariationAttributes(['AT001', 'AT002']);
        $element->setVariants([
            'SKU0000001' => false,
            'SKU0000002' => false,
            'SKU0000003' => true,
        ]);

        return $element;
    }

    protected function buildSetElement(): Product
    {
        $element = $this->buildBaseElement('Set', 1);
        $element->setClassificationCategory('CAT123', 'TestCatalog');
        $element->setSitemap(0.5);
        $element->setSetProducts(['PRODUCT123', 'PRODUCT456']);

        return $element;
    }

    protected function buildBundleElement(): Product
    {
        $element = $this->buildBaseElement('Bundle', 2);
        $element->setClassificationCategory('CAT123', 'TestCatalog');
        $element->setSitemap(0.5);
        $element->setBundleProducts([
            'SKU0000001' => 10,
            'SKU0000002' => 20,
        ]);
        $element->setTax(20);

        return $element;
    }

    protected function buildVariationElement(): Product
    {
        $element = $this->buildBaseElement('Variation', 3);
        $element->setSitemap(0.5);

        return $element;
    }
}
