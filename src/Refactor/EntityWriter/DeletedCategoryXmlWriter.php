<?php
namespace DemandwareXml\Refactor\EntityWriter;

use DemandwareXml\Refactor\Entity\DeletedCategory;
use DemandwareXml\Refactor\Xml\XmlWriter;

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
