<?php
namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\DeletedCategoryXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedCategory implements WriteableEntityInteface
{
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function write(XmlWriter $writer): void
    {
        (new DeletedCategoryXmlWriter($writer, $this))->write();
    }
}
