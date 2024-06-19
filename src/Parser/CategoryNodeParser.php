<?php

namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class CategoryNodeParser implements NodeParserInterface
{
    use CommonDetailsNodeParserTrait;

    public function __construct(protected XMLReader $reader)
    {
    }

    public function isMatch(): bool
    {
        return $this->reader->nodeType === XMLReader::ELEMENT && $this->reader->localName === 'category';
    }

    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element);
    }

    public function parse(): array
    {
        $element = new SimpleXMLElement($this->reader->readOuterXml());

        return [
            'id'   => (string) $element['category-id'],
            'data' => $this->getCommonDetails($element),
        ];
    }
}
