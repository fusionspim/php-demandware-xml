<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class AssignmentNodeParser implements NodeParserInterface
{
    public function __construct(protected XMLReader $reader)
    {
    }

    public function isMatch(): bool
    {
        return ($this->reader->nodeType === XMLReader::ELEMENT && $this->reader->localName === 'category-assignment');
    }

    public function parse(): array
    {
        $element = new SimpleXMLElement($this->reader->readOuterXml());

        $categoryId = (string) $element['category-id'];
        $primary    = (isset($element->{'primary-flag'}) ? ((string) $element->{'primary-flag'}) === 'true' : false);

        return [
            'id'   => (string) $element['product-id'],
            'data' => [$categoryId => $primary],
        ];
    }
}
