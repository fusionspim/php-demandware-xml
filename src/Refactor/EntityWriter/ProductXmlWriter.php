<?php
namespace DemandwareXml\Refactor\EntityWriter;

use DemandwareXml\Refactor\Entity\Product;
use DemandwareXml\Refactor\Xml\{XmlFormatter, XmlWriter};

class ProductXmlWriter
{
    private $writer;
    private $product;

    public function __construct(XmlWriter $writer, Product $product)
    {
        $this->writer  = $writer;
        $this->product = $product;
    }

    public function write(): void
    {
        $this->writer->startElement('product');
        $this->writer->ifNotEmpty()->writeAttribute('product-id', $this->product->id);
        $this->writer->ifNotEmpty()->writeElement('upc', $this->product->upc);
        $this->writer->ifNotEmpty()->writeElement('min-order-quantity', $this->product->minOrderQuantity);
        $this->writer->ifNotEmpty()->writeElement('step-quantity', $this->product->stepQuantity);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('display-name', $this->product->displayName, ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElementWithAttributes('long-description', XmlFormatter::sanitise($this->product->longDescription), ['xml:lang' => 'x-default']);
        $this->writer->ifNotEmpty()->writeElement('online-flag', XmlFormatter::fromBoolean($this->product->onlineFlag));
        $this->writer->ifNotEmpty()->writeElement('online-from', XmlFormatter::fromDateTime($this->product->onlineFrom));
        $this->writer->ifNotEmpty()->writeElement('online-to', XmlFormatter::fromDateTime($this->product->onlineTo));
        $this->writer->ifNotEmpty()->writeElement('available-flag', XmlFormatter::fromBoolean($this->product->availableFlag));
        $this->writer->ifNotEmpty()->writeElement('searchable-flag', XmlFormatter::fromBoolean($this->product->searchableFlag));
        $this->writer->ifNotEmpty()->writeElement('searchable-if-unavailable-flag', XmlFormatter::fromBoolean($this->product->searchableIfUnavailableFlag));
        $this->writeImages();
        $this->writer->ifNotEmpty()->writeElement('tax-class-id', $this->product->tax);
        $this->writeBrand();
        $this->writer->ifNotEmpty()->writeElement('search-rank', $this->product->searchRank);
        $this->writer->ifNotEmpty()->writeElement('sitemap-included-flag', XmlFormatter::fromBoolean($this->product->sitemapIncludedFlag));
        $this->writer->ifNotEmpty()->writeElement('sitemap-changefrequency', $this->product->sitemapChangeFrequency);
        $this->writer->ifNotEmpty()->writeElement('sitemap-priority', $this->product->sitemapPriority);
        $this->writePageAttributes();
        $this->writeCustomAttributes();
        $this->writeVariations();
        $this->writeBundleProducts();
        $this->writeSetProducts();
        $this->writeClassificationCategory();
        $this->writer->endElement();
    }

    /*
     * Bundles need to export a brand for online products, but not offline ones - it is set explicitly where required
     * Sets never export a brand
     *
     */
    private function writeBrand(): void
    {
        if (! XmlFormatter::isEmptyValue($this->product->brand)) {
            $this->writer->writeElement('brand', $this->product->brand);
        } else {
            if (! is_null($this->product->brand)) {
                $this->writer->writeEmptyElement('brand');
            }
        }
    }

    private function writeImages(): void
    {
        $images = XmlFormatter::filterEmptyValues($this->product->images);

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
        /*
        $pageAttributes = XmlFormatter::filterEmptyValues($this->product->pageAttributes);
        */

        $pageAttributes = $this->product->pageAttributes;

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
        if (count($this->product->customAttributes) === 0) {
            return;
        }

        ksort($this->product->customAttributes);

        $this->writer->startElement('custom-attributes');

        foreach ($this->product->customAttributes as $customAttribute) {
            $this->writer->writeEntity($customAttribute);
        }

        $this->writer->endElement();
    }

    private function writeVariations(): void
    {
        $attributes = XmlFormatter::filterEmptyValues($this->product->sharedVariationAttributes);
        $variants   = XmlFormatter::filterEmptyValues($this->product->variants);

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
        $attributes = XmlFormatter::filterEmptyValues($this->product->sharedVariationAttributes);

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
        $variants = XmlFormatter::filterEmptyValues($this->product->variants);

        if (count($variants) === 0) {
            return;
        }

        $this->writer->startElement('variants');

        foreach ($variants as $variantId => $variantDefault) {
            $attributes = ['product-id' => $variantId];

            if ($variantDefault) {
                $attributes['default'] = 'true';
            }

            $this->writer->writeEmptyElementWithAttributes('variant', $attributes);
        }

        $this->writer->endElement();
    }

    public function writeBundleProducts(): void
    {
        $products = XmlFormatter::filterEmptyValues($this->product->bundleProducts);

        if (count($products) === 0) {
            return;
        }

        $this->writer->startElement('bundled-products');

        foreach ($products as $productId => $quantity) {
            $this->writer->startElement('bundled-product');
            $this->writer->writeAttribute('product-id', $productId);
            $this->writer->writeElement('quantity', $quantity);
            $this->writer->endElement();
        }

        $this->writer->endElement();
    }

    public function writeSetProducts(): void
    {
        $products = XmlFormatter::filterEmptyValues($this->product->setProducts);

        if (count($products) === 0) {
            return;
        }

        $this->writer->startElement('product-set-products');

        foreach ($products as $productId) {
            $this->writer->writeEmptyElementWithAttributes('product-set-product', ['product-id' => $productId]);
        }

        $this->writer->endElement();
    }

    public function writeClassificationCategory(): void
    {
        if (XmlFormatter::isEmptyValue($this->product->classificationCatalogId) && XmlFormatter::isEmptyValue($this->product->classificationCategoryId)) {
            return;
        }

        if (! XmlFormatter::isEmptyValue($this->product->classificationCategoryId)) {
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
