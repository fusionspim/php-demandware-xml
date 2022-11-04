<?php

namespace DemandwareXml\Parser;

use SimpleXMLElement;

class ProductSimpleNodeParser extends ProductNodeParser implements NodeParserInterface
{
    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element, $skipAttribute = true);
    }
}
