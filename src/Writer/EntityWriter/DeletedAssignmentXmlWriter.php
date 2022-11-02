<?php

namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\DeletedAssignment;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedAssignmentXmlWriter
{
    public function __construct(private XmlWriter $writer, private DeletedAssignment $assignment)
    {
    }

    public function write(): void
    {
        $this->writer->writeEmptyElementWithAttributes('category-assignment', [
            'product-id' => $this->assignment->productId,
            'category-id' => $this->assignment->categoryId,
            'mode' => 'delete',
        ]);
    }
}
