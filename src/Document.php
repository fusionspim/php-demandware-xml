<?php
namespace DemandwareXml;

use DOMDocument;
use DOMElement;
use Throwable;

class Document
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
        'images',
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
        'primary-flag',
    ];

    /**
     * Create a new Demandware XML document for the specified catalog, in UTF-8 encoding
     */
    public function __construct(string $catalogId)
    {
        $this->dom                     = new DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput       = true;

        $this->root = $this->createElement('catalog');

        $this->addAttribute($this->root, 'xmlns', 'http://www.demandware.com/xml/impex/catalog/2006-10-31');
        $this->addAttribute($this->root, 'catalog-id', $catalogId);
    }

    /**
     * Add a new child of the root element.
     *
     * @throws XmlException
     */
    public function addObject(Base $object)
    {
        try {
            $root = $this->createElement($object->getElement());

            foreach ($object->getAttributes() as $key => $value) {
                $this->addAttribute($root, $key, $value);
            }

            // elements may be added in any order for ease of use, but when exporting they need to be in schema defined order
            foreach ($this->elementOrder as $name) {
                if (! isset($object->getElements()[$name])) {
                    continue;
                }

                $raw   = false;
                $value = $object->getElements()[$name];

                // If the value is an array then it contains the actual value and whether or not it should be escaped.
                if (is_array($value)) {
                    $raw   = $value['raw'];
                    $value = $value['value'];
                }

                $element = $this->createElement($name, $value, $raw);

                if ('display-name' === $name || 'long-description' === $name) {
                    $this->addAttribute($element, 'xml:lang', 'x-default');
                } elseif ('classification-category' === $name) {
                    // hack for `setClassification()`
                    $this->addAttribute($element, 'catalog-id', $object->getCatalog());
                }

                $root->appendChild($element);
            }

            $this->root->appendChild($root);
        } catch (Throwable $throwable) {
            throw new XmlException('Unable to create ' . $object->getElement() . ' node with details: ' . implode(', ', $object->getAttributes()), 0, $throwable);
        }
    }

    private function addAttribute(DOMElement $node, string $name, string $value)
    {
        $attribute = $this->dom->createAttribute($name);
        $attribute->value = Xml::escape($value);

        $node->appendChild($attribute);
    }

    private function createElement(string $name, $value = null, bool $raw = false)
    {
        if (is_null($value)) {
            $element = $this->dom->createElement($name);
        } elseif ('long-description' !== $name && strlen($value) > 0 && '<' === $value[0]) {
            // Skip long descriptions as they should always be inserted as elements and escaped.
            // This is terrible, but if first char looks like it's XML then just append the fragment.
            // This is to append pre-built XML from objects.
            $element = $this->dom->createDocumentFragment();
            $element->appendXML('<' . $name . '>' . $value . '</' . $name . '>');
        } else {
            $value   = ($raw ? Xml::sanitise($value) : Xml::escape($value));
            $element = $this->dom->createElement($name, $value);
        }

        return $element;
    }

    /**
     * Build the DOMDocument object by appending the root element to it.
     */
    private function build()
    {
        $this->dom->appendChild($this->root);
    }

    /**
     * Save the document to a path, which must include the appropriate extension (likely .xml)
     *
     * @throws XmlException
     */
    public function save(string $path): bool
    {
        $this->build();

        $results = $this->dom->save($path);

        Xml::validate($path);

        return $results > 0;
    }

    /**
     * Get the internal DOMDocument element.
     */
    public function getDomDocument(): DOMDocument
    {
        $this->build();

        return $this->dom;
    }
}
