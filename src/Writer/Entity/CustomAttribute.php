<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\CustomAttributeWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class CustomAttribute implements WriteableEntityInterface
{
    public function __construct(public string $id, public $value = null)
    {
    }

    public function write(XmlWriter $writer): void
    {
        (new CustomAttributeWriter($writer, $this))->write();
    }
}
