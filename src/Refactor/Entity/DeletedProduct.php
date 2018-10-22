<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\DeletedProductXmlWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;

class DeletedProduct implements WriteableEntityInteface
{
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function write(XmlWriter $writer): void
    {
        (new DeletedProductXmlWriter($writer, $this))->write();
    }
}
