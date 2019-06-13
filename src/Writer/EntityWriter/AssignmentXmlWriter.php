<?php
namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\Assignment;
use DemandwareXml\Writer\Xml\{XmlFormatter, XmlWriter};

class AssignmentXmlWriter
{
    private $writer;
    private $assignment;

    public function __construct(XmlWriter $writer, Assignment $assignment)
    {
        $this->writer     = $writer;
        $this->assignment = $assignment;
    }

    public function write(): void
    {
        $this->writer->startElement('category-assignment');
        $this->writer->writeAttribute('product-id', $this->assignment->productId);
        $this->writer->writeAttribute('category-id', $this->assignment->categoryId);
        $this->writer->ifNotEmpty()->writeElement('primary-flag', XmlFormatter::fromBoolean($this->assignment->primary));
        $this->writer->endElement();
    }
}
