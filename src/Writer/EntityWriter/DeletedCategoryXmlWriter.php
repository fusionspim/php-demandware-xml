<?php
namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\DeletedCategory;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedCategoryXmlWriter
{
    private $writer;
    private $category;

    public function __construct(XmlWriter $writer, DeletedCategory $category)
    {
        $this->writer   = $writer;
        $this->category = $category;
    }

    public function write(): void
    {
        $this->writer->writeEmptyElementWithAttributes('category', [
            'category-id' => $this->category->id,
            'mode'        => 'delete',
        ]);
    }
}
