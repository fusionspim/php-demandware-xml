<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\DeletedCategoryXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedCategory implements WriteableEntityInteface
{
    public function __construct(public string $id)
    {
    }

    public function write(XmlWriter $writer): void
    {
        (new DeletedCategoryXmlWriter($writer, $this))->write();
    }
}
