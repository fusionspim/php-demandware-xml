<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\VariantXmlWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;

class Variant implements WriteableEntityInteface
{
    public $id;
    public $displayValues = [];

    public function __construct(string $id)
    {
        $this->id = $id;
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
