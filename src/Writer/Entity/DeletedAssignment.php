<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\DeletedAssignmentXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedAssignment implements WriteableEntityInteface
{
    public function __construct(public string $productId, public string $categoryId) {}

    public function write(XmlWriter $writer): void
    {
        (new DeletedAssignmentXmlWriter($writer, $this))->write();
    }
}
