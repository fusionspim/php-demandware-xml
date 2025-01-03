<?php

namespace DemandwareXml\Test\Writer;

use DateTimeImmutable;
use DemandwareXml\Test\FixtureHelper;
use DemandwareXml\Writer\Entity\CustomAttribute;
use DemandwareXml\Writer\Entity\DeletedProduct;
use DemandwareXml\Writer\Entity\Product;
use DemandwareXml\Writer\EntityWriter\CustomAttributeWriter;
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

    public function test_product_xml_escaping(): void
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

    public function test_product_custom_attribute_value_limit(): void
    {
        $entity = $this->buildMinimalProductElement();
        $entity->addCustomAttributes([
            'test' => ['', ...array_fill(0, CustomAttributeWriter::MAX_VALUES, 'test'), 'missing'],
        ]);

        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();
        $xml->startDocument();
        $xml->startCatalog('TestCatalog');
        $xml->writeEntity($entity);
        $xml->endCatalog();
        $xml->endDocument();

        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('product-custom-attribute-value-limit.xml'),
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
        $xml->writeEntity($this->buildMinimalProductElement());
        $xml->writeEntity($this->buildSetElement());
        $xml->writeEntity($this->buildBundleElement());
        $xml->writeEntity($this->buildVariationElement());
        $xml->endCatalog();
        $xml->endDocument();

        return $xml;
    }

    protected function buildBaseElement(string $type, int $number = 0): Product
    {
        $invalidChar1 = chr(30); // Record Separator.
        $invalidChar2 = chr(2);  // Start Of Text.

        $element = new Product(mb_strtoupper($type) . '123');
        $element->setDisplayName($type . ' number 123');
        $element->setLongDescription('<b>' . $type . '</b> The description for an <i>example</i> ' . mb_strtolower($type) . '! • Bullet' . $invalidChar1 . 'Point');
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
            'http://example.com/' . mb_strtolower($type) . '/123',
            $type . ', test, example',
        );

        $element->addCustomAttributes([
            'type'         => 'Examples',
            'zzz'          => 'Should be exported last within' . $invalidChar2 . 'custom-attributes',
            'primaryImage' => mb_strtolower($type) . '-123.png',
            'multiWow'     => ['so', 'such', 'many', 'much', 'very'],
            'boolTrue'     => true,
            'boolFalse'    => false,
        ]);

        $element->setImages([mb_strtolower($type) . '-123.png']);

        return $element;
    }

    protected function buildMinimalProductElement(): Product
    {
        $element = new Product('PRD12340000');
        $element->setDisplayName('Minimal Product');
        $element->setLongDescription('This minimal product tests how empty fields are output');

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

        $element->addVariationGroups([
            'PRODUCT123-Red',
            'PRODUCT123-Yellow',
            'PRODUCT123-Green',
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
