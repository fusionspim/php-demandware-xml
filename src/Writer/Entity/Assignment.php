<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\AssignmentXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class Assignment implements WriteableEntityInteface
{
    public ?bool $primary = null;

    public function __construct(public string $productId, public string $categoryId)
    {
    }

    public function setPrimary(bool $primary): void
    {
        $this->primary = $primary;
    }

    public function write(XmlWriter $writer): void
    {
        (new AssignmentXmlWriter($writer, $this))->write();
    }
}
