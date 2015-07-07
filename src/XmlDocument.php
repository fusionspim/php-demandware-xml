<?php
namespace FusionsPIM\DemandwareXml;

use \DOMDocument;

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

    public function __construct($catalogId = null)
    {
        // @todo: currently unused - possibly remove?
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

    public function addObject(XmlAbstract $object)
    {
        $root = $this->createElement($object->getElement());

        foreach ($object->getAttributes() as $key => $value) {
            $this->addAttribute($root, $key, $value);
        }

        // elements may be added in any order for ease of use, but when exporting they need to be in schema defined order
        foreach ($this->elementOrder as $name) {
            if (! isset($object->getElements()[$name])) {
                continue;
            }

            $value   = $object->getElements()[$name];
            $element = $this->createElement($name, $value);

            if ('display-name' === $name || 'long-description' === $name) {
                $this->addAttribute($element, 'xml:lang', 'x-default');
            } elseif ('classification-category' === $name) {
                // hack for `setClassification()`
                $this->addAttribute($element, 'catalog-id', $object->getCatalog());
            }

            $root->appendChild($element);
        }

        $this->root->appendChild($root);
    }

    private function addAttribute($node, $name, $value)
    {
        $attribute = $this->dom->createAttribute($name);
        $attribute->value = static::escape($value);

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
            $element = $this->dom->createElement($name, static::escape($value));
        }

        return $element;
    }

    public static function escape($value)
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

        $schema = __DIR__ . '/../xsd/catalog.xsd';

        if (! file_exists($schema)) {
            throw new XmlException('Schema missing');
        }

        if (! $this->dom->schemaValidate(realpath($schema))) {
            throw new XmlException('XML validation failed: ' . basename($fileName) . "\n\n" . print_r(libxml_get_errors(), true));
        }
    }
}
