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
        $this->writeBrand();
        $this->writer->ifNotEmpty()->writeElement('sitemap-included-flag', Formatter::asBoolean($this->product->sitemapIncludedFlag));
        $this->writer->ifNotEmpty()->writeElement('sitemap-changefrequency', $this->product->sitemapChangeFrequency);
        $this->writer->ifNotEmpty()->writeElement('sitemap-priority', $this->product->sitemapPriority);
        $this->writePageAttributes();
        $this->writeCustomAttributes();
        $this->writeVariations();
        $this->writeClassificationCategory();
        $this->writer->endElement();
    }

    private function writeTax(): void
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

    private function writeBrand(): void
    {
        if (! Formatter::isEmpty($this->product->brand)) {
            $this->writer->writeElement('brand', $this->product->brand);
        } else {
            $this->writer->writeEmptyElement('brand');
        }
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
        if (
            Formatter::isEmpty($this->product->pageTitle) &&
            Formatter::isEmpty($this->product->pageDescription) &&
            Formatter::isEmpty($this->product->pageKeywords) &&
            Formatter::isEmpty($this->product->pageUrl)
        ) {
            return;
        }

        $this->writer->startElement('page-attributes');

        $pageAttributes = [
            'page-title'       => $this->product->pageTitle,
            'page-description' => $this->product->pageDescription,
            'page-keywords'    => $this->product->pageKeywords,
            'page-url'         => $this->product->pageUrl,
        ];

        foreach ($pageAttributes as $elemName => $elemContent) {
            if (! Formatter::isEmpty($elemContent)) {
                $this->writer->writeElementWithAttributes($elemName, $elemContent, ['xml:lang' => 'x-default']);
            } else {
                $this->writer->writeEmptyElementWithAttributes($elemName, ['xml:lang' => 'x-default']);
            }
        }

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

    private function writeVariations(): void
    {
        $attributes = Formatter::filterEmpty($this->product->sharedVariationAttributes);
        $variants   = Formatter::filterEmpty($this->product->variants);

        if (count($attributes) === 0 && count($variants) === 0) {
            return;
        }

        $this->writer->startElement('variations');
        $this->writeSharedVariationAttributes();
        $this->writeVariants();
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

    private function writeVariants(): void
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

    public function writeClassificationCategory(): void
    {
        if (Formatter::isEmpty($this->product->classificationCatalogId) && Formatter::isEmpty($this->product->classificationCategoryId)) {
            return;
        }

        if (! Formatter::isEmpty($this->product->classificationCategoryId)) {
            $this->writer->writeElementWithAttributes('classification-category', $this->product->classificationCategoryId, [
                'catalog-id' => $this->product->classificationCatalogId,
            ]);
        } else {
            $this->writer->writeEmptyElementWithAttributes('classification-category', [
                'catalog-id' => $this->product->classificationCatalogId,
            ]);
        }
    }
}
