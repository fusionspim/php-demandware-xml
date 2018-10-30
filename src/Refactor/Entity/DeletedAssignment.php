<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\DeletedAssignmentXmlWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;

class DeletedAssignment implements WriteableEntityInteface
{
    public $productId;
    public $categoryId;

    public function __construct(string $productId, string $categoryId)
    {
        $this->productId  = $productId;
        $this->categoryId = $categoryId;
    }

    public function write(XmlWriter $writer): void
    {
        (new DeletedAssignmentXmlWriter($writer, $this))->write();
    }
}
