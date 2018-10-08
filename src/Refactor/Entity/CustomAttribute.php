<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\CustomAttributeWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;

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
