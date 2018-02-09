<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;

class BundleSimpleNodeParser extends BundleNodeParser implements NodeParserInterface
{
    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element, true);
    }
}
