<?php
namespace DemandwareXml;

use DemandwareXml\Parser\{
    AssignmentNodeParser,
    BundleNodeParser,
    BundleSimpleNodeParser,
    CategoryNodeParser,
    CategorySimpleNodeParser,
    ProductNodeParser,
    ProductSimpleNodeParser,
    SetNodeParser,
    SetSimpleNodeParser,
    VariationNodeParser,
    VariationSimpleNodeParser
};
use XMLReader;

/**
 * Parses a Demandware XML file into the six main data structures expected, and returns arrays for ease of working with
 */
class Parser
{
    private $assignments = [];
    private $bundles     = [];
    private $categories  = [];
    private $products    = [];
    private $sets        = [];
    private $variations  = [];
    private $nodeParsers = [];

    /**
     * Create a new parser for the specified path, which will be validated against the XSD before parsing
     * For better speed and memory usage, parsing page/custom attributes and/or nodes can be skipped if not needed
     *
     * @throws XmlException
     */
    public function __construct(string $path, bool $skipAttributes = false)
    {
        // validate before opening with reader, since validation converts line breaks such as `</product>\n\n</product>`
        // to `</product>\n</product>` which avoids creating empty nodes or confusing `parse()` and skipping data :-o
        Xml::validate($path);

        $this->setNodeParsers($skipAttributes);

        $reader = new XMLReader;

        if (! $reader->open($path)) {
            throw new XmlException('Error opening ' . $path);
        }

        $this->parse($reader);
    }

    private function setNodeParsers(bool $skipAttributes)
    {
        if ($skipAttributes) {
            $this->nodeParsers = [
                AssignmentNodeParser::class,
                BundleSimpleNodeParser::class,
                CategorySimpleNodeParser::class,
                ProductSimpleNodeParser::class,
                SetSimpleNodeParser::class,
                VariationSimpleNodeParser::class,
            ];
        } else {
            $this->nodeParsers = [
                AssignmentNodeParser::class,
                BundleNodeParser::class,
                CategoryNodeParser::class,
                ProductNodeParser::class,
                SetNodeParser::class,
                VariationNodeParser::class,
            ];
        }
    }

    /**
     * Return an array containing product ids as keys, and an associative array of category ids mapped to whether primary for the values
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    /**
     * Return an array containing bundle ids as keys, and an associative array of name/value details for the values
     */
    public function getBundles(): array
    {
        return $this->bundles;
    }

    /**
     * Return an array containing category ids as keys, and an associative array of name/value details for the values
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Return an array containing product ids as keys, and an associative array of name/value details for the values
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * Return an array containing set ids as keys, and an associative array of name/value details for the values
     */
    public function getSets(): array
    {
        return $this->sets;
    }

    /**
     * Return an array containing variation ids as keys, and an associative array of name/value details for the values
     */
    public function getVariations(): array
    {
        return $this->variations;
    }

    private function parse(XMLReader $reader)
    {
        while ($reader->read()) {
            foreach ($this->nodeParsers as $class) {
                $nodeParser = new $class($reader);

                if (! $nodeParser->isMatch()) {
                    continue;
                }

                $result = $nodeParser->parse(); // @todo: Use array destructuring when on PHP 7.1.
                $key    = key($result);
                $value  = reset($result);

                switch ($class) {
                    case AssignmentNodeParser::class:
                        $this->assignments[$key][] = $value;
                        break;

                    case BundleNodeParser::class:
                    case BundleSimpleNodeParser::class:
                        $this->bundles[$key] = $value;
                        break;

                    case CategoryNodeParser::class:
                    case CategorySimpleNodeParser::class:
                        $this->categories[$key] = $value;
                        break;

                    case ProductNodeParser::class:
                    case ProductSimpleNodeParser::class:
                        $this->products[$key] = $value;
                        break;

                    case SetNodeParser::class:
                    case SetSimpleNodeParser::class:
                        $this->sets[$key] = $value;
                        break;

                    case VariationNodeParser::class:
                    case VariationSimpleNodeParser::class:
                        $this->variations[$key] = $value;
                        break;
                }
            }
        }
    }
}
