<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class AssignmentNodeParser implements NodeParserInterface
{
    protected $reader;

    public function __construct(XMLReader $reader)
    {
        $this->reader = $reader;
    }

    public function isMatch(): bool
    {
        return ($this->reader->nodeType === XMLReader::ELEMENT && $this->reader->localName === 'category-assignment');
    }

    public function parse(): array
    {
        $element = new SimpleXMLElement($this->reader->readOuterXml());

        $productId  = (string) $element['product-id'];
        $categoryId = (string) $element['category-id'];
        $primary    = (isset($element->{'primary-flag'}) ? ((string) $element->{'primary-flag'}) === 'true': false);

        return [$productId => [$categoryId => $primary]];
    }
}
