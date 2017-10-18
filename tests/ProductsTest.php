<?php
namespace DemandwareXml\Test;

use DemandwareXml\{Document, Product};

class ProductsTest extends AbstractTest
{
    protected $document;

    public function setUp()
    {
        $invalidChar = chr(30); // Record Separator.

        $document = new Document('TestCatalog');

        foreach (['Product', 'Set', 'Bundle', 'Variation'] as $index => $example) {
            $element = new Product(strtoupper($example) . '123');
            $element->setName($example . ' number 123');
            $element->setDescription('<b>' . $example . '</b> The description for an <i>example</i> ' . strtolower($example) . '! • Bullet' . $invalidChar . 'Point', true);
            $element->setUpc('50000000000' . $index);
            $element->setQuantities(); // include, but use defaults
            $element->setRank(1);
            $element->setSitemap(($example === 'Product' ? 1.0 : 0.5));
            $element->setBrand('SampleBrand™');
            $element->setFlags(true, false);
            $element->setDates('2015-01-23 01:23:45', '2025-01-23 01:23:45');
            $element->setPageAttributes(
                'Amazing ' . $example,
                'Buy our ' . $example . ' today!',
                $example . ', test, example',
                'http://example.com/' . strtolower($example) . '/123'
            );
            $element->setCustomAttributes([
                'type'         => 'Examples',
                'zzz'          => 'Should be exported last within custom-attributes',
                'primaryImage' => strtolower($example) . '-123.png',
                'multiWow'     => ['so', 'such', 'many', 'much', 'very'],
                'boolTrue'     => true,
                'boolFalse'    => false,
            ]);
            $element->setImages(strtolower($example) . '-123.png');

            // elements/attributes specific to bundle/set/product
            if ('Bundle' === $example) {
                $element->setProductQuantities(['SKU0000001' => 10, 'SKU0000002' => 20]);
                $element->setTax(20);
            } elseif ('Set' === $example) {
                $element->setProducts(['PRODUCT123', 'PRODUCT456']);
            } elseif ('Product' === $example) {
                $element->setSharedAttributes(['AT001', 'AT002']);
                $element->setVariants(['SKU0000001' => false, 'SKU0000002' => false, 'SKU0000003' => true]);
            }

            if ('Variation' !== $example) {
                $element->setClassification('CAT123', 'TestCatalog');
            }

            $document->addObject($element);
        }

        $this->document = $document;
    }

    public function tearDown()
    {
        $this->document = null;
    }

    public function testProductsXml()
    {
        $sampleXml = $this->loadFixture('products.xml');
        $outputXml = $this->document->getDomDocument()->saveXML();

        $this->assertXmlStringEqualsXmlString($sampleXml, $outputXml);
    }

    public function testProductsDeletedXml()
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
    public function testProductsInvalidEntitiesException()
    {
        $element = new Product('product123');
        $element->setName('product number 123 &bull;');

        $this->document->addObject($element);
        $this->document->save(__DIR__ . '/output/products.xml');
    }

    public function testProductsSaveXml()
    {
        $this->assertTrue($this->document->save(__DIR__ . '/output/products.xml'));
    }
}
