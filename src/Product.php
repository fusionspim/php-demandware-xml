<?php
namespace DemandwareXml;

class Product extends Base
{
    protected $element = 'product';
    private $catalog   = null;

    /**
     * Create a new <product> element, with $id populating the `product-id` attribute
     */
    public function __construct(string $id = null)
    {
        $this->attributes = ['product-id' => $id];
    }

    /**
     * Sets the `mode` attribute to "delete"
     */
    public function setDeleted(): void
    {
        $this->attributes['mode'] = 'delete';
    }

    /**
     * Populates the <upc> element
     */
    public function setUpc(string $value): void
    {
        $this->elements['upc'] = $value;
    }

    /**
     * Populates the <brand> element
     */
    public function setBrand(string $value): void
    {
        $this->elements['brand'] = $value;
    }

    /**
     * Populates the description of the product/set/bundle in the <long-description xml:lang="x-default"> element.
     * Accepts text and unencoded HTML (which will be encoded as UTF-8 entities).
     * @todo: Allow elements to be defined as raw or not and remove this hack.
     */
    public function setDescription(string $value, bool $raw = false): void
    {
        $this->elements['long-description'] = [
            'value' => $value,
            'raw'   => $raw,
        ];
    }

    /**
     * Populates the <search-rank> element, defaulting to "3"
     */
    public function setRank(int $value = 3): void
    {
        $this->elements['search-rank'] = $value;
    }

    /**
     * Populates the <min-order-quantity> and <step-quantity> elements, both defaulting to "1"
     */
    public function setQuantities(int $minOrder = 1, int $step = 1): void
    {
        $this->elements['min-order-quantity'] = $minOrder;
        $this->elements['step-quantity']      = $step;
    }

    /**
     * Populates the <classification-category> element with category id $value, and a `classification-category` attribute
     */
    public function setClassification(string $value, string $catalogId): void
    {
        // hack to later set the `catalog-id="$catalogId"` attribute within `createElement()`
        $this->catalog = $catalogId;

        $this->elements['classification-category'] = $value;
    }

    public function getCatalog(): string
    {
        return $this->catalog;
    }

    /**
     * Tax class in Demandware format, nn.dd as TAX_nn_dd - may only applies to bundles?
     */
    public function setTax(string $value): void
    {
        if (is_null($value)) {
            return;
        }

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

        $this->elements['tax-class-id'] = $value;
    }

    /**
     * Only applies to complex products (aka Master Variants)
     */
    public function setSharedAttributes(array $ids = []): void
    {
        $xml = '';

        foreach ($ids as $id) {
            $xml .= '<shared-variation-attribute
                        variation-attribute-id="' . Xml::escape($id) . '"
                        attribute-id="' . Xml::escape($id) . '"
                    ></shared-variation-attribute>';
        }

        $this->elements['attributes'] = $xml;

        $this->addVariations();
    }

    /**
     * Only applies to complex products (aka Master Variants)
     */
    public function setVariants(array $variants = []): void
    {
        $xml = '';

        foreach ($variants as $id => $default) {
            $xml .= '<variant product-id="' . Xml::escape($id) . '"' . ($default ? ' default="true"' : '') . '/>' . PHP_EOL;
        }

        $this->elements['variants'] = $xml;

        $this->addVariations();
    }

    /**
     * @todo: eliminate! hack for now that groups attributes and variants elements into variations once both available
     */
    private function addVariations(): void
    {
        if (! isset($this->elements['attributes']) || ! isset($this->elements['variants'])) {
            return;
        }

        $xml  = '<attributes>' . $this->elements['attributes'] . '</attributes>';
        $xml .= '<variants>' . $this->elements['variants'] . '</variants>';

        $this->elements['variations'] = $xml;

        unset($this->elements['attributes']);
        unset($this->elements['variants']);
    }

    /**
     * Only applies to Bundles
     */
    public function setProductQuantities(array $variations = []): void
    {
        $xml = '';

        foreach ($variations as $id => $quantity) {
            $xml .= '<bundled-product product-id="' . Xml::escape($id) . '">';
            $xml .= '<quantity>' . Xml::escape($quantity) . '</quantity>';
            $xml .= '</bundled-product>' . PHP_EOL;
        }

        $this->elements['bundled-products'] = $xml;
    }

    /**
     * Only applies to Sets
     */
    public function setProducts(array $products = []): void
    {
        $xml = '';

        foreach ($products as $id) {
            $xml .= '<product-set-product product-id="' . Xml::escape($id) . '" />' . PHP_EOL;
        }

        $this->elements['product-set-products'] = $xml;
    }

    public function setImages(string $path, string $viewType = 'large'): void
    {
        $xml  = '<image-group view-type="' . Xml::escape($viewType) . '">' . PHP_EOL;
        $xml .= '<image path="' . Xml::escape($path) . '" />' . PHP_EOL;
        $xml .= '</image-group>' . PHP_EOL;

        $this->elements['images'] = $xml;
    }
}
