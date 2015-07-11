<?php
namespace FusionsPIM\DemandwareXml;

use \DOMDocument;

class Xml
{
    /**
     * Escapes the value suitable for inclusion in XML and converts booleans to 'true'/'false' strings
     *
     * @param $value
     * @return string
     */
    public static function escape($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } else {
            $value = html_entity_decode($value); // not sure why, other than back compat

            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
        }
    }

    /**
     * Validates the XML file against the specified XSD schema
     * Also formats/normalizes the file to improve human readability and ease diff'ing
     *
     * @param $filePath
     * @param $schemaPath
     * @throws XmlException
     */
    public static function validate($filePath, $schemaPath)
    {
        if (! file_exists($filePath)) {
            throw new XmlException('XML file missing');
        }

        if (! file_exists($schemaPath)) {
            throw new XmlException('XSD schema missing');
        }

        libxml_use_internal_errors(true);

        // possibly more efficient to pass a $dom object rather than save/reload, but cleaner to assume already saved
        $dom                     = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->load($filePath);
        $dom->normalizeDocument();
        $dom->save($filePath);

        if (! $dom->schemaValidate(realpath($schemaPath))) {
            throw new XmlException('XML validation failed: ' . basename($filePath) . "\n\n" . print_r(libxml_get_errors(), true));
        }
    }
}
