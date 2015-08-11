<?php
namespace DemandwareXml\Test;

use \DemandwareXml\Parser;

// note: don't need to parse variants, so no test for those!
// rebuild fixtures: file_put_contents(__DIR__ . '/fixtures/categories.json', json_encode($parser->categories(), JSON_PRETTY_PRINT) . PHP_EOL);
class ParserTest extends AbstractTest
{
    public function testAssignmentsParser()
    {
        $parser   = $this->getFixtureParser('assignments.xml');
        $expected = $this->loadJsonFixture('assignments.json');

        $this->assertEquals($expected, $parser->assignments());
    }

    public function testBundlesParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('bundles.json');

        $this->assertEquals($expected, $parser->bundles());
    }

    public function testCategoriesParser()
    {
        $parser   = $this->getFixtureParser('categories.xml');
        $expected = $this->loadJsonFixture('categories.json');

        $this->assertEquals($expected, $parser->categories());
    }

    public function testProductsParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('products.json');

        $this->assertEquals($expected, $parser->products());
    }

    public function testSetsParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('sets.json');

        $this->assertEquals($expected, $parser->sets());
    }

    public function testVariationsParser()
    {
        $parser   = $this->getFixtureParser('products.xml');
        $expected = $this->loadJsonFixture('variations.json');

        $this->assertEquals($expected, $parser->variations());
    }

    protected function getFixtureParser($filename)
    {
        return new Parser(__DIR__ . '/fixtures/' . $filename);
    }
}
