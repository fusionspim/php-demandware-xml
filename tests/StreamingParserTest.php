<?php
namespace DemandwareXml\Test;

use DemandwareXml\StreamingParser;
use Generator;
use PHPUnit\Framework\TestCase;

// note: don't need to parse variants, so no test for those!
// rebuild fixtures: file_put_contents(__DIR__ . '/fixtures/categories.json', json_encode($parser->categories(), JSON_PRETTY_PRINT) . PHP_EOL);
class StreamingParserTest extends TestCase
{
    use FixtureHelper;

    /**
     * @expectedException              \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp /Fatal: xmlParseEntityRef: no name in invalid-products.xml/
     */
    public function testParserValidateInvalidXml()
    {
        (new StreamingParser(__DIR__ . '/fixtures/invalid-products.xml'))->validate();
    }

    /**
     * @expectedException              \DemandwareXml\XmlException
     * @expectedExceptionMessageRegExp /Error: Element '{.*}upc': This element is not expected. Expected is one of \( {.*}step-quantity, {.*}display-name, {.*}short-description, {.*}long-description, {.*}store-receipt-name, {.*}store-tax-class, {.*}store-force-price-flag, {.*}store-non-inventory-flag, {.*}store-non-revenue-flag, {.*}store-non-discountable-flag \). in invalid-schema-products.xml/
     */
    public function testParserValidateInvalidSchemaXml()
    {
        (new StreamingParser(__DIR__ . '/fixtures/invalid-schema-products.xml'))->validate();
    }

    /**
     * @expectedException        \DemandwareXml\XMLException
     * @expectedExceptionMessage XML file does not exist: fake-products.xml
     */
    public function testParserValidateFileDoesNotExist()
    {
        (new StreamingParser(__DIR__ . '/fixtures/fake-products.xml'))->validate();
    }

    public function testParserValidate()
    {
        $this->assertTrue((new StreamingParser(__DIR__ . '/fixtures/products.xml'))->validate());
    }

    public function testEmptyParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/empty.xml');

        $this->assertEmpty(iterator_to_array($parser->getAssignments()));
        $this->assertEmpty(iterator_to_array($parser->getBundles()));
        $this->assertEmpty(iterator_to_array($parser->getCategories()));
        $this->assertEmpty(iterator_to_array($parser->getPhotos()));
        $this->assertEmpty(iterator_to_array($parser->getProducts()));
        $this->assertEmpty(iterator_to_array($parser->getSets()));
        $this->assertEmpty(iterator_to_array($parser->getVariations()));
    }

    public function testAssignmentsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/assignments.xml');

        $assignments = [];

        foreach ($parser->getAssignments() as $productId => $assignment) {
            $assignments[$productId][] = $assignment;
        }

        $this->assertEquals(
            $this->loadJsonFixture('assignments.json'),
            $assignments
        );
    }

    public function testBundlesParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');

        $this->assertEquals(
            $this->loadJsonFixture('bundles.json'),
            iterator_to_array($parser->getBundles())
        );
    }

    public function testSimpleBundlesParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');
        $parser->skipAttributes(true);

        $this->assertEquals(
            $this->loadJsonFixture('bundles-simple.json'),
            iterator_to_array($parser->getBundles())
        );
    }

    public function testCategoriesParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/categories.xml');

        $this->assertEquals(
            $this->loadJsonFixture('categories.json'),
            iterator_to_array($parser->getCategories())
        );
    }

    public function testSimpleCategoriesParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/categories.xml');
        $parser->skipAttributes(true);

        $this->assertEquals(
            $this->loadJsonFixture('categories-simple.json'),
            iterator_to_array($parser->getCategories())
        );
    }

    public function testPhotosParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/photos.xml');

        $this->assertEquals(
            $this->loadJsonFixture('photos.json'),
            iterator_to_array($parser->getPhotos())
        );
    }

    public function testProductsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');

        $this->assertEquals(
            $this->loadJsonFixture('products.json'),
            iterator_to_array($parser->getProducts())
        );
    }

    public function testSimpleProductsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');
        $parser->skipAttributes(true);

        $this->assertEquals(
            $this->loadJsonFixture('products-simple.json'),
            iterator_to_array($parser->getProducts())
        );
    }

    public function testSetsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');

        $this->assertEquals(
            $this->loadJsonFixture('sets.json'),
            iterator_to_array($parser->getSets())
        );
    }

    public function testSimpleSetsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');
        $parser->skipAttributes(true);

        $this->assertEquals(
            $this->loadJsonFixture('sets-simple.json'),
            iterator_to_array($parser->getSets())
        );
    }

    public function testVariationsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');

        $this->assertEquals(
            $this->loadJsonFixture('variations.json'),
            iterator_to_array($parser->getVariations())
        );
    }

    public function testSimpleVariationsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/products.xml');
        $parser->skipAttributes(true);

        $this->assertEquals(
            $this->loadJsonFixture('variations-simple.json'),
            iterator_to_array($parser->getVariations())
        );
    }
}
