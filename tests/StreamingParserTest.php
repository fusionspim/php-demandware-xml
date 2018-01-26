<?php
namespace DemandwareXml\Test;

use DemandwareXml\StreamingParser;
use PHPUnit\Framework\TestCase;

// note: don't need to parse variants, so no test for those!
// rebuild fixtures: file_put_contents(__DIR__ . '/fixtures/categories.json', json_encode($parser->categories(), JSON_PRETTY_PRINT) . PHP_EOL);
class StreamingParserTest extends TestCase
{
    use FixtureHelper;

    /**
     * @expectedException        \DemandwareXml\XmlException
     * @expectedExceptionMessage Fatal: xmlParseEntityRef: no name in invalid-products.xml on line 8 column 113
     */
    public function testParserValidateInvalidXml()
    {
        (new StreamingParser(__DIR__ . '/fixtures/invalid-products.xml'))->validate();
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

    public function testAssignmentsParser()
    {
        $parser = new StreamingParser(__DIR__ . '/fixtures/assignments.xml');

        $this->assertEquals(
            $this->loadJsonFixture('assignments.json'),
            StreamingParser::toArrayGroupedByKey($parser->getAssignments())
        );
    }
}
