<?php
namespace DemandwareXml\Refactor\Serializer;

use DemandwareXml\Refactor\Entity\EntityInterface;
use DemandwareXml\Refactor\Helper\Formatter;
use DemandwareXml\Refactor\Xml\Writer;

class ProductSerializer implements SerializerInterface
{
    private $writer;
    private $product;

    public function __construct(Writer $writer, EntityInterface $product)
    {
        $this->writer  = $writer;
        $this->product = $product;
    }

    public function serialize(): void
    {
        $this->writer->startElement('product');
        $this->writer->ifNotEmpty()->writeAttribute('product-id', $this->product->id);
        $this->writer->ifNotEmpty()->writeElement('upc', $this->product->upc);
        $this->writer->ifNotEmpty()->writeElement('min-order-quantity', $this->product->minOrderQuantity);
        $this->writer->ifNotEmpty()->writeElement('step-quantity', $this->product->stepQuantity);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('display-name', $this->product->displayName, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('long-description', $this->product->longDescription, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElement('online-flag', Formatter::asBoolean($this->product->onlineFlag));
        $this->writer->ifNotEmpty()->writeElement('online-from', Formatter::asDateTime($this->product->onlineFrom));
        $this->writer->ifNotEmpty()->writeElement('online-to', Formatter::asDateTime($this->product->onlineTo));
        $this->writer->ifNotEmpty()->writeElement('available-flag', Formatter::asBoolean($this->product->availableFlag));
        $this->writer->ifNotEmpty()->writeElement('searchable-flag', Formatter::asBoolean($this->product->searchableFlag));
        $this->writer->ifNotEmpty()->writeElement('searchable-if-unavailable-flag', Formatter::asBoolean($this->product->searchableIfUnavailableFlag));
        $this->writeImages();
        $this->writeTax();
        $this->writer->ifNotEmpty()->writeElement('brand', $this->product->brand);
        $this->writer->ifNotEmpty()->writeElement('sitemap-included-flag', Formatter::asBoolean($this->product->sitemapIncludedFlag));
        $this->writer->ifNotEmpty()->writeElement('sitemap-changefrequency', $this->product->sitemapChangeFrequency);
        $this->writer->ifNotEmpty()->writeElement('sitemap-priority', $this->product->sitemapPriority);
        $this->writePageAttributes();
        $this->writeCustomAttributes();
        $this->writer->startElement('variations'); // need to check bits in between exist before we render.
        $this->writeSharedVariationAttributes();
        $this->writeVariants();
        $this->writer->endElement();
        $this->writer->writeElementWithAttributes('classification-category', $this->product->classificationCategory, ['catalog-id' => $this->writer->catalogId]);
        $this->writer->endElement();
    }

    private function writeTax()
    {
        if ($this->product->tax === null) {
            return;
        }

        $value = $this->product->tax;

        if ($value == 0) {
            $value = 'TAX_0';
        } else {
            $value = number_format($value, 2);

            // not sure why has two underscores?
            if ($value < 1) {
                $value = 'TAX__' . str_replace('0.', '', $value);
            } else {
                $value = 'TAX_' . str_replace('.', '_', $value);
            }
        }

        $this->writer->writeElement('tax-class-id', $value);
    }

    private function writeImages(): void
    {
        $images = Formatter::filterEmpty($this->product->images);

        if (count($images) === 0) {
            return;
        }

        $this->writer->startElement('images');
        $this->writer->startElement('image-group');
        $this->writer->writeAttribute('view-type', 'large');

        foreach ($images as $image) {
            $this->writer->ifNotEmpty()->writeEmptyElementWithAttributes('image', ['path' => $image]);
        }

        $this->writer->endElement();
        $this->writer->endElement();
    }

    private function writePageAttributes(): void
    {
        $this->writer->startElement('page-attributes');
        $this->writer->ifNotEmpty()->writeElementWithAttributes('page-title', $this->product->pageTitle, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('page-description', $this->product->pageDescription, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('page-keywords', $this->product->pageKeywords, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('page-url', $this->product->pageUrl, ['xml:lang' => 'x-default']);
        $this->writer->endElement();
    }

    private function writeCustomAttributes(): void
    {
        if (count($this->product->customAttributes) === 0) {
            return;
        }

        $this->writer->startElement('custom-attributes');

        foreach ($this->product->customAttributes as $customAttribute) {
            $this->writer->writeEntity($customAttribute);
        }

        $this->writer->endElement();
    }

    private function writeSharedVariationAttributes(): void
    {
        $attributes = Formatter::filterEmpty($this->product->sharedVariationAttributes);

        if (count($attributes) === 0) {
            return;
        }

        $this->writer->startElement('attributes');

        foreach ($attributes as $id) {
            $this->writer->writeEmptyElementWithAttributes('shared-variation-attribute', [
                'variation-attribute-id' => $id,
                'attribute-id'           => $id,
            ]);
        }

        $this->writer->endElement();
    }

    private function writeVariants()
    {
        $variants = Formatter::filterEmpty($this->product->variants);

        if (count($variants) === 0) {
            return;
        }

        $this->writer->startElement('variants');

        foreach ($variants as $variant) {
            $default = ($variant === $this->product->defaultVariant ? ['default' => 'true'] : []);
            $this->writer->writeEmptyElementWithAttributes('variant', array_merge(['product-id' => $variant], $default));
        }

        $this->writer->endElement();
    }
}
