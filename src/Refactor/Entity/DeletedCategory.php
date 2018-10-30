<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\DeletedCategoryXmlWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;

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
