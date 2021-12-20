<?php
namespace DemandwareXml\Writer\Entity;

use DateTimeInterface;
use DemandwareXml\Writer\EntityWriter\ProductXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;
use InvalidArgumentException;

class Product implements WriteableEntityInteface
{
    public string|null $upc = null;
    public int|null $minOrderQuantity = null;
    public int|null $stepQuantity = null;
    public string|null $displayName = null;
    public string|null $longDescription = null;
    public bool|null $onlineFlag = null;
    public DateTimeInterface|null $onlineFrom = null;
    public DateTimeInterface|null $onlineTo = null;
    public bool|null $availableFlag = null;
    public bool|null $searchableFlag = null;
    public bool|null $searchableIfUnavailableFlag = null;
    public string|null $tax = null;
    public array|null $images                    = [];
    public string|null $imageViewType = null;
    public string|null $brand = null;
    public int|null $searchRank = null;
    public bool|null $sitemapIncludedFlag = null;
    public string|null $sitemapChangeFrequency = null;
    public string|null $sitemapPriority = null;
    public array $pageAttributes            = [];
    public array $customAttributes          = [];
    public array $sharedVariationAttributes = [];
    public array $variants                  = [];
    public array|null $bundleProducts = null;
    public array|null $setProducts = null;
    public string|null $classificationCategoryId = null;
    public string|null $classificationCatalogId = null;
    public array $variationGroups           = [];

    public function __construct(public string $id)
    {
    }

    public function setUpc(string $upc): void
    {
        $this->upc = $upc;
    }

    public function setQuantities(int $minOrderQuantity = 1, int $stepQuantity = 1): void
    {
        $this->minOrderQuantity = $minOrderQuantity;
        $this->stepQuantity     = $stepQuantity;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function setLongDescription(string $longDescription): void
    {
        $this->longDescription = $longDescription;
    }

    public function setOnlineFlag(bool $onlineFlag): void
    {
        $this->onlineFlag = $onlineFlag;
    }

    public function setOnlineFromTo(DateTimeInterface|null $from, DateTimeInterface|null $to): void
    {
        if ($from !== null) {
            $this->onlineFrom = $from;
        }

        if ($to !== null) {
            $this->onlineTo = $to;
        }
    }

    public function setSearchableFlags(bool|null $availableFlag, bool|null $searchableFlag, bool|null $searchableIfUnavailableFlag): void
    {
        if ($availableFlag !== null) {
            $this->availableFlag = $availableFlag;
        }

        if ($searchableFlag !== null) {
            $this->searchableFlag = $searchableFlag;
        }

        if ($searchableIfUnavailableFlag !== null) {
            $this->searchableIfUnavailableFlag = $searchableIfUnavailableFlag;
        }
    }

    public function setTax(float|null $tax): void
    {
        if ($tax === null) {
            return;
        }

        if (empty($tax)) {
            $tax = 'TAX_0';
        } else {
            $tax = number_format($tax, 2);

            // Not sure why this needs to have two underscores.
            if ($tax < 1) {
                $tax = 'TAX__' . str_replace('0.', '', $tax);
            } else {
                $tax = 'TAX_' . str_replace('.', '_', $tax);
            }
        }

        $this->tax = $tax;
    }

    public function setImages(array $images, string $imageViewType = 'large'): void
    {
        $this->imageViewType = $imageViewType;
        $this->images        = $images;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function setSearchRank(int $searchRank = 3): void
    {
        $this->searchRank = $searchRank;
    }

    public function setSitemap(float|null $sitemapPriority = null, bool $sitemapIncludedFlag = true, string $sitemapChangeFrequency = 'weekly'): void
    {
        if ($sitemapPriority !== null && $sitemapPriority > 1) {
            throw new InvalidArgumentException('Sitemap priority must be 1.0 or less');
        }

        if ($sitemapPriority !== null) {
            $this->sitemapPriority = number_format($sitemapPriority, 1);
        }

        $this->sitemapIncludedFlag    = $sitemapIncludedFlag;
        $this->sitemapChangeFrequency = $sitemapChangeFrequency;
    }

    public function setPageAttributes(string|null $pageTitle, string|null $pageDescription, string|null $pageKeywords, string|null $pageUrl): void
    {
        $this->pageAttributes = [
            'page-title'       => $pageTitle,
            'page-description' => $pageDescription,
            'page-keywords'    => $pageKeywords,
            'page-url'         => $pageUrl,
        ];
    }

    public function addCustomAttribute(CustomAttribute $customAttribute): void
    {
        $this->customAttributes[$customAttribute->id] = $customAttribute;
    }

    public function addCustomAttributes(array $map): void
    {
        foreach ($map as $id => $value) {
            $this->addCustomAttribute(new CustomAttribute($id, $value));
        }
    }

    public function setSharedVariationAttributes(array $sharedVariationAttributes): void
    {
        $this->sharedVariationAttributes = $sharedVariationAttributes;
    }

    public function setVariants(array $variants): void
    {
        foreach ($variants as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Variants array keys must all be strings');
            }

            if (! is_bool($value)) {
                throw new InvalidArgumentException('Variants array values must all be booleans');
            }
        }

        $this->variants = $variants;
    }

    // Applies to bundles only.
    public function setBundleProducts(array $bundleProducts): void
    {
        foreach ($bundleProducts as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Bundle Product array keys must all be strings');
            }

            if (! is_int($value)) {
                throw new InvalidArgumentException('Bundle Product array values must all be integers');
            }
        }

        $this->bundleProducts = $bundleProducts;
    }

    // Applies to sets only.
    public function setSetProducts(array $setProducts): void
    {
        foreach ($setProducts as $value) {
            if (! is_string($value)) {
                throw new InvalidArgumentException('Set Product array values must all be strings');
            }
        }

        $this->setProducts = $setProducts;
    }

    public function setClassificationCategory(string $classificationCategoryId, string $classificationCatalogId): void
    {
        $this->classificationCategoryId = $classificationCategoryId;
        $this->classificationCatalogId  = $classificationCatalogId;
    }

    public function addVariationGroups(array $variationGroups): void
    {
        foreach ($variationGroups as $variationGroup) {
            $this->addVariationGroup($variationGroup);
        }
    }

    public function addVariationGroup(string $variationGroup): void
    {
        $this->variationGroups[] = $variationGroup;
    }

    public function write(XmlWriter $writer): void
    {
        (new ProductXmlWriter($writer, $this))->write();
    }
}
