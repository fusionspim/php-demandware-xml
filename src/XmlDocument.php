<?php
namespace FusionsPIM\DemandwareXml;

use \DOMDocument;
use \Exception;

// You can add elements in whatever order makes most sense in your code, the class will automatically export them in the sequence defined by the XSD schema. To ease comparison of exports, all root elements and custom attributes are exported sorted by their first attribute value.
// @todo: this (whole folder) and xsd's should eventually be moved to a generic `DemandwareXml` library
// @todo: add test files/scripts, with 'zzzz' => 'to this should be last custom attr' etc. and massive file to know what been scaled to and memory usage
// @todo: XmlDiff class to compare two Demandware XML files and report on differences?
class XmlDocument
{
    private $dom;

    // the order elements must appear in the xml, as specified by the xsd
    private $elementOrder = [
        'upc',
        'min-order-quantity',
        'step-quantity',
        'display-name',
        'long-description',
        'online-flag',
        'online-from',
        'online-to',
        'parent',
        'available-flag',
        'searchable-flag',
        'searchable-if-unavailable-flag',
        'template',
        'tax-class-id',
        'brand',
        'search-rank',
        'sitemap-included-flag',
        'sitemap-changefrequency',
        'sitemap-priority',
        'page-attributes',
        'custom-attributes',
        'bundled-products',
        'product-set-products',
        'variations',
        'classification-category',
        'variation-attribute-values',
        'primary-flag'
    ];

    // or empty if inventory
    public function __construct($catalogId = null)
    {
        $this->isInventory = is_null($catalogId);

        $this->dom                     = new DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput       = true;

        $this->root = $this->createElement($this->isInventory ? 'inventory' : 'catalog');

        $this->addAttribute($this->root, 'xmlns', 'http://www.demandware.com/xml/impex/' . ($this->isInventory ? 'inventory/2007-05-31' : 'catalog/2006-10-31'));

        if (! $this->isInventory) {
            $this->addAttribute($this->root, 'catalog-id', $catalogId);
        }
    }

    public function addObject(XmlInterface $object)
    {
        $root = $this->createElement($object->element);

        foreach ($object->attributes as $key => $value) {
            $this->addAttribute($root, $key, $value);
        }

        // interface lets us add elements in any order for ease of use, but when adding to doc need to be in order expected by schema
        foreach ($this->elementOrder as $name) {
            if (! isset($object->elements[$name])) {
                continue;
            }

            $value = $object->elements[$name];

            // @todo: nested elements such as page|custom-attributes, i.e. anything with a comment of "dirty, but works and encapsulates the implementation within library for now..."
            if (is_array($value)) {
                // echo '<!--' . print_r($value, true) . '-->';
                continue;
            }

            $element = $this->createElement($name, $value);

            $root->appendChild($element);
        }

        $this->root->appendChild($root);
    }

    private function addAttribute($node, $name, $value)
    {
        $attribute = $this->dom->createAttribute($name);
        $attribute->value = $this->escape($value);

        $node->appendChild($attribute);
    }

    private function createElement($name, $value = null)
    {
        if (is_null($value)) {
            $element = $this->dom->createElement($name);
        } elseif (strlen($value) > 0 && '<' === $value[0]) {
            // this is terrible, but if first char looks like is xml, just append the fragment
            $element = $this->dom->createDocumentFragment();

            $element->appendXML('<' . $name . '>' . $value . '</' . $name . '>');
        } else {
            $element = $this->dom->createElement($name, $this->escape($value));
        }

        // @todo: in_array known to be slow, simple string checks should suffice, perhaps specify when add element instead?
        if (in_array($name, ['display-name', 'long-description'])) {
            $this->addAttribute($element, 'xml:lang', 'x-default');
        }

        return $element;
    }

    // @todo: this is simpler, but could escape/cast when add values to avoid many is_bool calls if inefficient?
    private function escape($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } else {
            $value = html_entity_decode($value); // not sure why, other than back compat

            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
        }
    }

    public function save($fileName)
    {
        $this->dom->appendChild($this->root);
        $this->dom->save($fileName);

        $this->validate($fileName);
    }

    private function validate($fileName)
    {
        libxml_use_internal_errors(true);

        // @todo: not sure why need to reload etc. but causes error if don't?
        $this->dom->load($fileName);
        $this->dom->normalizeDocument();
        $this->dom->save($fileName);

        $schema = '../xsd/catalog.xsd';

        if (! file_exists($schema)) {
            throw new Exception('Schema missing');
        }

        if (! $this->dom->schemaValidate(realpath($schema))) {
            throw new Exception('XML validation failed: ' . basename($fileName) . "\n\n" . print_r(libxml_get_errors(), true));
        }
    }
}
