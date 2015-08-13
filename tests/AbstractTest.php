<?php
namespace DemandwareXml\Test;

use \PHPUnit_Framework_TestCase;
use \DOMDocument;

abstract class AbstractTest extends PHPUnit_Framework_TestCase
{
    protected function loadFixture($filename)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $dom->load(__DIR__ . '/fixtures/' . ltrim($filename, '/'));

        return $dom;
    }

    protected function loadJsonFixture($filename)
    {
        return json_decode(file_get_contents(__DIR__ . '/fixtures/' . ltrim($filename, '/')), true);
    }
}
