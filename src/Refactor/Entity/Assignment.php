<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\AssignmentXmlWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;

class Assignment implements WriteableEntityInteface
{
    public $productId;
    public $categoryId;
    public $primary;

    public function __construct(string $productId, string $categoryId)
    {
        $this->productId  = $productId;
        $this->categoryId = $categoryId;
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
