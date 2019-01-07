<?php
namespace DemandwareXml\Refactor\EntityWriter;

use DemandwareXml\Refactor\Entity\DeletedAssignment;
use DemandwareXml\Refactor\Xml\XmlWriter;

class DeletedAssignmentXmlWriter
{
    private $writer;
    private $assignment;

    public function __construct(XmlWriter $writer, DeletedAssignment $assignment)
    {
        $this->writer     = $writer;
        $this->assignment = $assignment;
    }

    public function write(): void
    {
        $this->writer->writeEmptyElementWithAttributes('category-assignment', [
            'product-id'  => $this->assignment->productId,
            'category-id' => $this->assignment->categoryId,
            'mode'        => 'delete',
        ]);
    }
}
