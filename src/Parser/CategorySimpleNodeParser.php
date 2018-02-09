<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;

class CategorySimpleNodeParser extends CategoryNodeParser implements NodeParserInterface
{
    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element, true);
    }
}
