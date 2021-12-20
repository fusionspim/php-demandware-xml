<?php
namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\VariantXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class Variant implements WriteableEntityInteface
{
    public array $displayValues = [];

    public function __construct(public string $id)
    {
    }

    public function addDisplayValue(string $value, string $displayValue): void
    {
        $this->displayValues[$value] = $displayValue;
    }

    public function write(XmlWriter $writer): void
    {
        (new VariantXmlWriter($writer, $this))->write();
    }
}
