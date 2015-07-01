<?php
namespace FusionsPIM\DemandwareXml;

class XmlProduct extends XmlAbstract
{
    public $element = 'product';

    public function __construct($id = null)
    {
        $this->attributes = ['product-id' => $id];
    }

    public function setUpc($value)
    {
        $this->elements['upc'] = $value;
    }

    public function setBrand($value)
    {
        $this->elements['brand'] = $value;
    }

    public function setDescription($value)
    {
        $this->elements['long-description'] = $value;
    }

    public function setRank($value = 3)
    {
        $this->elements['search-rank'] = $value;
    }

    public function setQuantities($minOrder = 1, $step = 1)
    {
        $this->elements['min-order-quantity'] = $minOrder;
        $this->elements['step-quantity']      = $step;
    }

    // @todo: set the attribute <classification-category catalog-id="ASDACatalog">
    public function setClassification($value, $catalogId)
    {
        $this->elements['classification-category'] = $value;
    }

    // nn.dd formatted as TAX_nn_dd - only applies to bundles?
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

    // @todo: dirty, but works and encapsulates the implementation within library for now...
    // only applies to actual products
    public function setSharedAttributes(array $ids = [])
    {
        $xml = '';

        foreach ($ids as $id) {
            $xml .= '<shared-variation-attribute
                        variation-attribute-id="' . $this->escape($id) . '"
                        attribute-id="' . $this->escape($id) . '"
                    ></shared-variation-attribute>';
        }

        $this->elements['attributes'] = $xml;

        $this->addVariations();
    }

    // @todo: dirty, but works and encapsulates the implementation within library for now...
    // only applies to actual products
    public function setVariants(array $variants = [])
    {
        $xml = '';

        foreach ($variants as $id => $default) {
            $xml .= '<variant product-id="' . $this->escape($id) . '"' . ($default ? ' default="true"' : '') . '/>' . PHP_EOL;
        }

        $this->elements['variants'] = $xml;

        $this->addVariations();
    }

    // @todo: eliminate! hack for now that groups attributes and variants elements into variations once both available
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

    // @todo: dirty, but works and encapsulates the implementation within library for now...
    // only applies to bundles
    public function setProductQuantities(array $variations = [])
    {
        $xml = '';

        foreach ($variations as $id => $quantity) {
            $xml .= '<bundled-product product-id="' . $this->escape($id) . '">';
            $xml .= '<quantity>' . $this->escape($quantity) . '</quantity>';
            $xml .= '</bundled-product>' . PHP_EOL;
        }

        $this->elements['bundled-products'] = $xml;
    }

    // @todo: dirty, but works and encapsulates the implementation within library for now...
    // only applies to products
    public function setProducts(array $products = [])
    {
        $xml = '';

        foreach ($products as $id) {
            $xml .= '<product-set-product product-id="' . $this->escape($id) . '" />' . PHP_EOL;
        }

        $this->elements['product-set-products'] = $xml;
    }
}
