<?php
namespace DemandwareXml\Test;

use \DemandwareXml\Parser;

// note: don't need to parse variants, so no test for those!
// rebuild fixtures: file_put_contents(__DIR__ . '/fixtures/categories.json', json_encode($parser->categories(), JSON_PRETTY_PRINT) . PHP_EOL);
class ParserTest extends AbstractTest
{
    public function testMixedParser()
    {
        $parser = $this->getFixtureParser('mixed.xml', true);

        $this->assertEquals($this->loadJsonFixture('mixed-products.json'), $parser->getProducts());
        $this->assertEquals($this->loadJsonFixture('mixed-categories.json'), $parser->getCategories());
        $this->assertEquals($this->loadJsonFixture('mixed-assignments.json'), $parser->getAssignments());
    }

    public function testMixedParserWithExclusions()
    {
        $parser = $this->getFixtureParser('mixed.xml', true, ['product', 'category-assignment']);

        $this->assertEmpty($parser->getProducts());
        $this->assertEquals($this->loadJsonFixture('mixed-categories.json'), $parser->getCategories());
        $this->assertEmpty($parser->getAssignments());
    }

    public function testAssignmentsParser()
    {
        $parser   = $this->getFixtureParser('assignments.xml');
        $expected = $this->loadJsonFixture('assignments.json');

        $this->assertEquals($expected, $parser->getAssignments());
    }

    public function testBundlesParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('bundles.json');

        $this->assertEquals($expected, $parser->getBundles());
    }

    public function testSimpleBundlesParser()
    {
        $parser   = $this->getFixtureParser('products.xml', true);
        $expected = $this->loadJsonFixture('bundles-simple.json');

        $this->assertEquals($expected, $parser->getBundles());
    }

    public function testCategoriesParser()
    {
        $parser   = $this->getFixtureParser('categories.xml');
        $expected = $this->loadJsonFixture('categories.json');

        $this->assertEquals($expected, $parser->getCategories());
    }

    public function testSimpleCategoriesParser()
    {
        $parser   = $this->getFixtureParser('categories.xml', true);
        $expected = $this->loadJsonFixture('categories-simple.json');

        $this->assertEquals($expected, $parser->getCategories());
    }

    public function testProductsParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('products.json');

        $this->assertEquals($expected, $parser->getProducts());
    }

    public function testSimpleProductsParser()
    {
        $parser   = $this->getFixtureParser('products.xml', true);
        $expected = $this->loadJsonFixture('products-simple.json');

        $this->assertEquals($expected, $parser->getProducts());
    }

    public function testSetsParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('sets.json');

        $this->assertEquals($expected, $parser->getSets());
    }

    public function testSimpleSetsParser()
    {
        $parser   = $this->getFixtureParser('products.xml', true);
        $expected = $this->loadJsonFixture('sets-simple.json');

        $this->assertEquals($expected, $parser->getSets());
    }

    public function testVariationsParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('variations.json');

        $this->assertEquals($expected, $parser->getVariations());
    }

    public function testSimpleVariationsParser()
    {
        $parser   = $this->getFixtureParser('products.xml', true);
        $expected = $this->loadJsonFixture('variations-simple.json');

        $this->assertEquals($expected, $parser->getVariations());
    }

    protected function getFixtureParser($filename, $skipAttributes = false, array $excludedNodes = [])
    {
        return new Parser(__DIR__ . '/fixtures/' . $filename, $skipAttributes, $excludedNodes);
    }
}
