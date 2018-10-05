<?php
namespace DemandwareXml\Test\Refactor\Xml;

use DemandwareXml\Refactor\Xml\XmlWriter;
use PHPUnit\Framework\TestCase;

class XmlWriterTest extends TestCase
{
    private function getMemoryXmlWriter(): XmlWriter
    {
        $xml = new XmlWriter;
        $xml->openMemory();
        $xml->setIndentDefaults();

        return $xml;
    }

    private function getFileXmlWriter(string $filename): XmlWriter
    {
        $xml = new XmlWriter;
        $xml->openFile($filename);
        $xml->setIndentDefaults();

        return $xml;
    }

    public function test_simple_catalog_memory()
    {
        $xml = $this->getMemoryXmlWriter();
        $xml->startCatalog('TestCatalog');
        $xml->writeElement('test', 'FOOBAR');
        $xml->endCatalog();

        $this->assertXmlStringEqualsXmlString(
            '<catalog xmlns="http://www.demandware.com/xml/impex/catalog/2006-10-31" catalog-id="TestCatalog">' .
            '  <test>FOOBAR</test>' .
            '</catalog>',
            $xml->outputMemory(true)
        );
    }

    public function test_simple_catalog_file()
    {
        $output = TEST_OUTPUT_DIR . '/catalog_simple.xml';

        $xml = $this->getFileXmlWriter($output);
        $xml->startCatalog('TestCatalog');
        $xml->writeElement('test', 'FOOBAR');
        $xml->endCatalog();
        $xml->flush(true);

        $this->assertXmlStringEqualsXmlString(
            '<catalog xmlns="http://www.demandware.com/xml/impex/catalog/2006-10-31" catalog-id="TestCatalog">' .
            '  <test>FOOBAR</test>' .
            '</catalog>',
            file_get_contents($output)
        );
    }

    public function test_write_element_with_attributes()
    {
        $xml = $this->getMemoryXmlWriter();
        $xml->writeElementWithAttributes('test', 'FOOBAR', ['foo' => 'bar']);
        $this->assertXmlStringEqualsXmlString('<test foo="bar">FOOBAR</test>', $xml->outputMemory(true));
    }

    public function test_write_empty_element()
    {
        $xml = $this->getMemoryXmlWriter();
        $xml->writeEmptyElement('test');
        $this->assertXmlStringEqualsXmlString('<test/>', $xml->outputMemory(true));
    }

    public function test_write_empty_element_with_attributes()
    {
        $xml = $this->getMemoryXmlWriter();
        $xml->writeEmptyElementWithAttributes('test', ['foo' => 'bar']);
        $this->assertXmlStringEqualsXmlString('<test foo="bar"/>', $xml->outputMemory(true));
    }
}
