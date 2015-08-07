<?php
namespace DemandwareXml\Test;

use \DemandwareXml\Parser;

// note: don't care about/use variants for now
// to regen fixtures: file_put_contents($path . 'categories.json', json_encode($parser->categories(), JSON_PRETTY_PRINT) . PHP_EOL);
class ParserTest extends AbstractTest
{
    public function testAssignmentsParser()
    {
        $path     = __DIR__ . '/fixtures/';
        $parser   = new Parser($path . 'assignments.xml');
        $expected = json_decode(file_get_contents($path . 'assignments.json'), true);

        $this->assertEquals($expected, $parser->assignments());
    }

    public function testCategoriesParser()
    {
        $path     = __DIR__ . '/fixtures/';
        $parser   = new Parser($path . 'categories.xml');
        $expected = json_decode(file_get_contents($path . 'categories.json'), true);

        $this->assertEquals($expected, $parser->categories());
    }

    public function testProductsParser()
    {
        $path     = __DIR__ . '/fixtures/';
        $parser   = new Parser($path . 'products.xml');
        $expected = json_decode(file_get_contents($path . 'products.json'), true);

        $this->assertEquals($expected, $parser->products());
    }
}
