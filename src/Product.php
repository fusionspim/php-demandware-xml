<?php
namespace FusionsPIM\DemandwareXml;

class Product extends Base
{
    protected $element = 'product';
    private $catalog   = null;

    /**
     * Create a new <product> element, with $id populating the `product-id` attribute
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->attributes = ['product-id' => $id];
    }

    /**
     * Populates the <upc> element
     *
     * @param $value
     */
    public function setUpc($value)
    {
        $this->elements['upc'] = $value;
    }

    /**
     * Populates the <brand> element
     *
     * @param $value
     */
    public function setBrand($value)
    {
        $this->elements['brand'] = $value;
    }

    /**
     * Populates the description of the product/set/bundle in the <long-description xml:lang="x-default"> element
     *
     * @param $value
     */
    public function setDescription($value)
    {
        $this->elements['long-description'] = $value;
    }

    /**
     * Populates the <search-rank> element, defaulting to "3"
     *
     * @param int $value
     */
    public function setRank($value = 3)
    {
        $this->elements['search-rank'] = $value;
    }

    /**
     * Populates the <min-order-quantity> and <step-quantity> elements, both defaulting to "1"
     * @param int $minOrder
     * @param int $step
     */
    public function setQuantities($minOrder = 1, $step = 1)
    {
        $this->elements['min-order-quantity'] = $minOrder;
        $this->elements['step-quantity']      = $step;
    }

    /**
     * Populates the <classification-category> element with category id $value, and a `classification-category` attribute
     *
     * @param $value
     * @param $catalogId
     */
    public function setClassification($value, $catalogId)
    {
        // hack to later set the `catalog-id="$catalogId"` attribute within `createElement()`
        $this->catalog = $catalogId;

        $this->elements['classification-category'] = $value;
    }

    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Tax class in Demandware format, nn.dd as TAX_nn_dd - may only applies to bundles?
     *
     * @param $value
     */
    public function setTax($value)
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
     *
     * @param array $ids
     */
    public function setSharedAttributes(array $ids = [])
    {
        $xml = '';

        foreach ($ids as $id) {
            $xml .= '<shared-variation-attribute
                        variation-attribute-id="' . Document::escape($id) . '"
                        attribute-id="' . Document::escape($id) . '"
                    ></shared-variation-attribute>';
        }

        $this->elements['attributes'] = $xml;

        $this->addVariations();
    }

    /**
     * Only applies to complex products (aka Master Variants)
     *
     * @param array $variants
     */
    public function setVariants(array $variants = [])
    {
        $xml = '';

        foreach ($variants as $id => $default) {
            $xml .= '<variant product-id="' . Document::escape($id) . '"' . ($default ? ' default="true"' : '') . '/>' . PHP_EOL;
        }

        $this->elements['variants'] = $xml;

        $this->addVariations();
    }

    /**
     * @todo: eliminate! hack for now that groups attributes and variants elements into variations once both available
     */
    private function addVariations()
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
     *
     * @param array $variations
     */
    public function setProductQuantities(array $variations = [])
    {
        $xml = '';

        foreach ($variations as $id => $quantity) {
            $xml .= '<bundled-product product-id="' . Document::escape($id) . '">';
            $xml .= '<quantity>' . Document::escape($quantity) . '</quantity>';
            $xml .= '</bundled-product>' . PHP_EOL;
        }

        $this->elements['bundled-products'] = $xml;
    }

    /**
     * Only applies to Sets
     *
     * @param array $products
     */
    public function setProducts(array $products = [])
    {
        $xml = '';

        foreach ($products as $id) {
            $xml .= '<product-set-product product-id="' . Document::escape($id) . '" />' . PHP_EOL;
        }

        $this->elements['product-set-products'] = $xml;
    }
}
