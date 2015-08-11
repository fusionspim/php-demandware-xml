<?php
namespace DemandwareXml;

use \SimpleXMLElement;
use \XMLReader;

/**
 * Parses a Demandware XML file into the six main data structures expected, and returns arrays for ease of working with
 *
 * @package DemandwareXml
 * @todo: not currently needed, but iterator (or better, generators? http://evertpot.com/switching-to-generators/) may be more memory efficient than arrays for large files
 */
class Parser
{
    private $assignments = [];
    private $bundles     = [];
    private $categories  = [];
    private $products    = [];
    private $sets        = [];
    private $variations  = [];

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
     * Return an array containing product ids as keys, and an associative array of category ids mapped to whether primary for the values
     *
     * @return array
     */
    public function assignments()
    {
        return $this->assignments;
    }

    /**
     * Return an array containing bundle ids as keys, and an associative array of name/value details for the values
     *
     * @return array
     */
    public function bundles()
    {
        return $this->bundles;
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

    /**
     * Return an array containing set ids as keys, and an associative array of name/value details for the values
     *
     * @return array
     */
    public function sets()
    {
        return $this->sets;
    }

    /**
     * Return an array containing variation ids as keys, and an associative array of name/value details for the values
     *
     * @return array
     */
    public function variations()
    {
        return $this->variations;
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
                case 'category':
                    $this->addCategory($element);
                    break;

                case 'category-assignment':
                    $this->addAssignment($element);
                    break;

                // we can determine the specifics of what the product is used for by checking for grouping elements
                case 'product':
                    $id = (string) $element['product-id'];
                    // @todo: $details = $this->commonDetails($element);

                    if (isset($element->{'bundled-products'})) {
                        $this->addBundle($id, $element);
                    } elseif (isset($element->{'product-set-products'})) {
                        $this->addSet($id, $element);
                    } elseif (isset($element->{'variations'})) {
                        $this->addProduct($id, $element);
                    } else {
                        $this->addVariation($id, $element);
                    }

                    break;
            }

            $reader->next();
        }
    }

    private function addAssignment(SimpleXMLElement $element)
    {
        $productId  = (string) $element['product-id'];
        $categoryId = (string) $element['category-id'];
        $primary    = (isset($element->{'primary-flag'}) ? (boolean) $element->{'primary-flag'} : false);

        $this->assignments[$productId][] = [$categoryId => $primary];
    }

    private function addBundle($id, SimpleXMLElement $element)
    {
        $details = $this->commonDetails($element);

        foreach ($element->{'bundled-products'}->{'bundled-product'} as $variation) {
            $quantity = (isset($variation->{'quantity'}) ? (int) $variation->{'quantity'} : 0);

            $details['variations'][(string) $variation['product-id']] = $quantity;
        }

        $this->bundles[$id] = $details;
    }

    private function addCategory(SimpleXMLElement $element)
    {
        $this->categories[(string) $element['category-id']] = $this->commonDetails($element);
    }

    private function addProduct($id, SimpleXMLElement $element)
    {
        $details = $this->commonDetails($element);

        foreach ($element->{'variations'}->{'variants'}->{'variant'} as $variation) {
            $details['variations'][(string) $variation['product-id']] = isset($variation['default']);
        }

        $this->products[$id] = $details;
    }

    private function addSet($id, SimpleXMLElement $element)
    {
        $details = $this->commonDetails($element);

        foreach ($element->{'product-set-products'}->{'product-set-product'} as $product) {
            $details['products'][] = (string) $product['product-id'];
        }

        $this->sets[$id] = $details;
    }

    // @todo: may not need function?
    private function addVariation($id, SimpleXMLElement $element)
    {
        $this->variations[$id] = $this->commonDetails($element);
    }

    private function commonDetails(SimpleXMLElement $element)
    {
        $details    = [
            'attributes' => $this->customAttributes($element),
            'page'       => $this->pageAttributes($element)
        ];

        $map = [
            'description'    => 'long-description',
            'name'           => 'display-name',
            'start'          => 'online-from',
            'classification' => 'classification-category',
            'online'         => 'online-flag',
            'searchable'     => 'searchable-flag',
            'parent'         => 'parent',
            'tax'            => 'tax-class-id',
        ];

        foreach ($map as $name => $source) {
            $cleansed = html_entity_decode(trim((string) $element->{$source}));

            if (strlen($cleansed) > 0) {
                $details[$name] = $cleansed;
            }
        }

        // if they exist, online/searchable will always be a true/false string, so cast for ease of use
        foreach (['online', 'searchable'] as $name) {
            if (isset($details[$name])) {
                $details[$name] = (boolean) $details[$name];
            }
        }

        // convert the tax string to a meaningful number
        if (isset($details['tax'])) {
            $details['tax'] = (float) str_replace(['TAX_', '_'], ['', '.'], $details['tax']);
        }

        ksort($details);

        return $details;
    }

    private function customAttributes($element)
    {
        if (! isset($element->{'custom-attributes'}->{'custom-attribute'})) {
            return [];
        }

        $attributes = [];

        foreach ($element->{'custom-attributes'}->{'custom-attribute'} as $attribute) {
            if (isset($attribute->{'value'})) {
                $value = [];

                foreach ($attribute->{'value'} as $item) {
                    $value[] = trim((string) $item);
                }
            } else {
                $value = trim((string) $attribute);

                // cast strings to booleans (only needed for single values, as multi-value booleans make no sense)
                if ('true' === $value || 'false' === $value) {
                    $value = (boolean) $value;
                }
            }

            $attributes[(string) $attribute['attribute-id']] = $value;
        }

        ksort($attributes);

        return $attributes;
    }

    private function pageAttributes($element)
    {
        $attributes = [];

        foreach (['title', 'description', 'keywords', 'url'] as $part) {
            $value = html_entity_decode(trim((string) $element->{'page-attributes'}->{'page-' . $part}));

            if (strlen($value) > 0) {
                $attributes[$part] = $value;
            }
        }

        return $attributes;
    }
}
