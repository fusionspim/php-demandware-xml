<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\DeletedProductXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedProduct implements WriteableEntityInteface
{
    public function __construct(public string $id) {}

    public function write(XmlWriter $writer): void
    {
        (new DeletedProductXmlWriter($writer, $this))->write();
    }
}
