<?php
namespace DemandwareXml\Refactor\EntityWriter;

use DemandwareXml\Refactor\Entity\Category;
use DemandwareXml\Refactor\Xml\{XmlFormatter, XmlWriter};

class CategoryXmlWriter
{
    private $writer;
    private $category;

    public function __construct(XmlWriter $writer, Category $category)
    {
        $this->writer   = $writer;
        $this->category = $category;
    }

    public function write(): void
    {
        $this->writer->startElement('category');
        $this->writer->ifNotEmpty()->writeAttribute('category-id', $this->category->id);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('display-name', $this->category->displayName, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElement('online-flag', XmlFormatter::fromBoolean($this->category->onlineFlag));
        $this->writer->ifNotEmpty()->writeElement('online-from', XmlFormatter::fromDateTime($this->category->onlineFrom));
        $this->writer->ifNotEmpty()->writeElement('online-to', XmlFormatter::fromDateTime($this->category->onlineTo));
        $this->writer->ifNotEmpty()->writeElement('parent', $this->category->parentId);
        $this->writeTemplate();
        $this->writer->ifNotEmpty()->writeElement('sitemap-included-flag', XmlFormatter::fromBoolean($this->category->sitemapIncludedFlag));
        $this->writer->ifNotEmpty()->writeElement('sitemap-changefrequency', $this->category->sitemapChangeFrequency);
        $this->writer->ifNotEmpty()->writeElement('sitemap-priority', $this->category->sitemapPriority);
        $this->writePageAttributes();
        $this->writeCustomAttributes();
        $this->writer->endElement();
    }

    private function writeTemplate(): void
    {
        if (! XmlFormatter::isEmptyValue($this->category->template)) {
            $this->writer->writeElement('template', $this->category->template);
        } else {
            $this->writer->writeEmptyElement('template');
        }
    }

    private function writePageAttributes(): void
    {
        /*
        $pageAttributes = XmlFormatter::filterEmptyValues($this->category->pageAttributes);
        */

        $pageAttributes = $this->category->pageAttributes;

        if (count($pageAttributes) === 0) {
            return;
        }

        $this->writer->startElement('page-attributes');

        foreach ($pageAttributes as $elemName => $elemContent) {
            if (! XmlFormatter::isEmptyValue($elemContent)) {
                $this->writer->writeElementWithAttributes($elemName, $elemContent, ['xml:lang' => 'x-default']);
            } else {
                $this->writer->writeEmptyElementWithAttributes($elemName, ['xml:lang' => 'x-default']);
            }
        }

        $this->writer->endElement();
    }

    private function writeCustomAttributes(): void
    {
        if (count($this->category->customAttributes) === 0) {
            return;
        }

        ksort($this->category->customAttributes);

        $this->writer->startElement('custom-attributes');

        foreach ($this->category->customAttributes as $customAttribute) {
            $this->writer->writeEntity($customAttribute);
        }

        $this->writer->endElement();
    }
}
