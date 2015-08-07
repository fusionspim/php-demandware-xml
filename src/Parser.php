<?php
namespace DemandwareXml;

use \SimpleXMLElement;
use \XMLReader;

// @todo: not a problem now, but the arrays could use a lot of memory for big files, so an iterator may be more efficient
class Parser
{
    private $assignments = [];
    private $categories  = [];
    private $products    = [];

    /**
     * Create a new parser for the specified path, which will be validated against the XSD before parsing
     *
     * @param $path
     * @throws Exception
     * @throws XmlException
     */
    public function __construct($path)
    {
        $reader = new XMLReader;

        if (! $reader->open($path)) {
            throw new XmlException('Error opening ' . $path);
        }

        Xml::validate($path);

        $this->parse($reader);
    }

    /**
     * Return an array containing product ids as keys, and an array of category ids for the values
     *
     * @return array
     */
    public function assignments()
    {
        return $this->assignments;
    }

    /**
     * Return an array containing category ids as keys, and an associative array of name/value details for the values
     *
     * @return array
     */
    public function categories()
    {
        return $this->categories;
    }

    /**
     * Return an array containing product ids as keys, and an associative array of name/value details for the values
     *
     * @return array
     */
    public function products()
    {
        return $this->products;
    }

    private function parse(XMLReader $reader)
    {
        while ($reader->read()) {
            $nodeName = $reader->localName;

            if (! in_array($nodeName, ['product', 'category', 'category-assignment'])) {
                continue;
            }

            $element = new SimpleXMLElement($reader->readOuterXML());

            switch ($nodeName) {
                case 'product':
                    $this->addProduct($element);
                    break;

                case 'category':
                    $this->addCategory($element);
                    break;

                case 'category-assignment':
                    $this->addAssignment($element);
                    break;
            }

            $reader->next();
        }
    }

    private function addAssignment(SimpleXMLElement $element)
    {
        $productId  = (string) $element['product-id'];
        $categoryId = (string) $element['category-id'];

        $this->assignments[$productId][] = $categoryId;
    }

    private function addCategory(SimpleXMLElement $element)
    {
        $details           = $this->arrayFromKeyElements($element);
        $details['parent'] = (string) $element->{'parent'};
        $details['name']   = trim((string) $element->{'display-name'});

        $this->categories[(string) $element['category-id']] = $details;
    }

    private function addProduct(SimpleXMLElement $element)
    {
        $id         = (string) $element['product-id'];
        $onlineFrom = (string) $element->{'online-from'};

        $this->products[$id] = $this->arrayFromKeyElements($element);

        if (strlen($onlineFrom) > 0) {
            $this->products[$id]['online-from'] = $onlineFrom;
        }
    }

    private function arrayFromKeyElements($element)
    {
        $attributes = [];

        foreach ($element->{'custom-attributes'}->{'custom-attribute'} as $attribute) {
            if (isset($attribute->{'value'})) {
                $value = [];

                foreach ($attribute->{'value'} as $item) {
                    $value[] = trim((string) $item);
                }
            } else {
                $value = trim((string) $attribute);
            }

            $attributes[(string) $attribute['attribute-id']] = $value;
        }

        return ['online-flag' => (boolean) $element->{'online-flag'}, 'attributes' => $attributes];
    }
}
