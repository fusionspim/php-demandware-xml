<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class CategoryNodeParser implements NodeParserInterface
{
    use CommonDetailsNodeParserTrait;

    protected $reader;

    public function __construct(XMLReader $reader)
    {
        $this->reader = $reader;
    }

    public function isMatch(): bool
    {
        return ($this->reader->nodeType === XMLReader::ELEMENT && $this->reader->localName === 'category');
    }

    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element);
    }

    public function parse(): array
    {
        $element = new SimpleXMLElement($this->reader->readOuterXml());

        return [(string) $element['category-id'] => $this->getCommonDetails($element)];
    }
}
