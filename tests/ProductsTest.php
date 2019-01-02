<?php
namespace DemandwareXml\Test;

use DemandwareXml\{Document, Product};
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    use FixtureHelper;

    public function testProductsXml(): void
    {
        $this->assertXmlStringEqualsXmlString(
            $this->loadFixture('products.xml'),
            $this->buildDocument()->getDomDocument()->saveXML()
        );
    }

    public function testProductsSaveXml(): void
    {
        $this->assertTrue($this->buildDocument()->save(__DIR__ . '/output/products.xml'));
    }

    public function testProductsDeletedXml(): void
    {
        $document = new Document('TestCatalog');

        $element = new Product('PRODUCT123');
        $element->setDeleted();
        $document->addObject($element);

        $element = new Product('VARIATION123');
        $element->setDeleted();
        $document->addObject($element);

        $sampleXml = $this->loadFixture('products-deleted.xml');
        $outputXml = $document->getDomDocument()->saveXML();

        $this->assertXmlStringEqualsXmlString($sampleXml, $outputXml);
    }

    /**
     * @expectedException               \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp  /Entity 'bull' not defined/
     */
    public function testProductsInvalidEntitiesException(): void
    {
        $document = new Document('TestCatalog');

        $element = new Product('product123');
        $element->setName('product number 123 &bull;');

        $document->addObject($element);
        $document->save(__DIR__ . '/output/products.xml');
    }

    /**
     * @expectedException        \DemandwareXml\XmlException
     * @expectedExceptionMessag  Unable to create product node containing invalid XML (variation123)
     */
    public function testProductsInvalidAddObjectException(): void
    {
        $document = new Document('TestCatalog');

        $element = new Product('variation123');
        $element->setBrand('This is an example brand');
        $element->setName('product number 123 &bull;');
        $element->setCustomAttributes(['foobar' => 'This is invalid &bull;']);

        $document->addObject($element);
        $document->save(__DIR__ . '/output/products.xml');
    }

    public function testSetSitemap(): void
    {
        $element = new Product('PRODUCT123');
        $element->setSitemap(0.5);
        $this->assertEquals([
            'sitemap-priority'        => '0.5',
            'sitemap-included-flag'   => true,
            'sitemap-changefrequency' => 'weekly',
        ], $element->getElements());

        $element = new Product('PRODUCT123');
        $element->setSitemap(1, null, null);
        $this->assertEquals([
            'sitemap-priority' => '1.0',
        ], $element->getElements());

        $element = new Product('PRODUCT123');
        $element->setSitemap(null, true, 'daily');
        $this->assertEquals([
            'sitemap-included-flag'   => true,
            'sitemap-changefrequency' => 'daily',
        ], $element->getElements());
    }

    /**
     * @expectedException       \DemandwareXml\XmlException
     * @expectedExceptionMessage Sitemap priority must be 1.0 or less
     */
    public function testInvalidSitemapPriority(): void
    {
        $element = new Product('PRODUCT123');
        $element->setSitemap(42.5);
    }

    protected function buildDocument(): Document
    {
        $document = new Document('TestCatalog');
        $document->addObject($this->buildProductElement());
        $document->addObject($this->buildSetElement());
        $document->addObject($this->buildBundleElement());
        $document->addObject($this->buildVariationElement());

        return $document;
    }

    protected function buildBaseElement(string $type, int $number = 0): Product
    {
        $invalidChar = chr(30); // Record Separator.

        $element = new Product(mb_strtoupper($type) . '123');
        $element->setName($type . ' number 123');
        $element->setDescription('<b>' . $type . '</b> The description for an <i>example</i> ' . mb_strtolower($type) . '! • Bullet' . $invalidChar . 'Point', true);
        $element->setUpc('50000000000' . $number);
        $element->setQuantities(); // include, but use defaults
        $element->setRank(1);
        $element->setBrand('SampleBrand™');
        $element->setFlags(true, false);
        $element->setDates('2015-01-23 01:23:45', '2025-01-23 01:23:45');

        $element->setPageAttributes(
            'Amazing ' . $type,
            'Buy our ' . $type . ' today!',
            $type . ', test, example',
            'http://example.com/' . mb_strtolower($type) . '/123'
        );

        $element->setCustomAttributes([
            'type'         => 'Examples',
            'zzz'          => 'Should be exported last within custom-attributes',
            'primaryImage' => mb_strtolower($type) . '-123.png',
            'multiWow'     => ['so', 'such', 'many', 'much', 'very'],
            'boolTrue'     => true,
            'boolFalse'    => false,
        ]);

        $element->setImages(mb_strtolower($type) . '-123.png');

        return $element;
    }

    protected function buildProductElement(): Product
    {
        $element = $this->buildBaseElement('Product');
        $element->setClassification('CAT123', 'TestCatalog');
        $element->setSitemap(1.0);
        $element->setImages('product-123.png');
        $element->setSharedAttributes(['AT001', 'AT002']);
        $element->setVariants(['SKU0000001' => false, 'SKU0000002' => false, 'SKU0000003' => true]);

        return $element;
    }

    protected function buildSetElement(): Product
    {
        $element = $this->buildBaseElement('Set', 1);
        $element->setClassification('CAT123', 'TestCatalog');
        $element->setSitemap(0.5);
        $element->setProducts(['PRODUCT123', 'PRODUCT456']);

        return $element;
    }

    protected function buildBundleElement(): Product
    {
        $element = $this->buildBaseElement('Bundle', 2);
        $element->setClassification('CAT123', 'TestCatalog');
        $element->setSitemap(0.5);
        $element->setProductQuantities(['SKU0000001' => 10, 'SKU0000002' => 20]);
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
