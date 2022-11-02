<?php

namespace DemandwareXml\Test;

use DOMDocument;

trait FixtureHelper
{
    protected function loadFixture(string $filename): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $dom->load(TEST_FIXTURE_DIR.'/'.ltrim($filename, '/'));

        return $dom->saveXML();
    }

    protected function loadJsonFixture(string $filename): array
    {
        return json_decode(file_get_contents(TEST_FIXTURE_DIR.'/'.ltrim($filename, '/')), true, 512, JSON_THROW_ON_ERROR);
    }
}
