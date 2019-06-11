<?php
namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\CustomAttributeWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class CustomAttribute implements WriteableEntityInteface
{
    public $id;
    public $value;

    public function __construct(string $id, $value = null)
    {
        $this->id    = $id;
        $this->value = $value;
    }

    public function write(XmlWriter $writer): void
    {
        (new CustomAttributeWriter($writer, $this))->write();
    }
}
