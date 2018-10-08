<?php
namespace DemandwareXml\Refactor\Entity;

use DateTimeInterface;
use DemandwareXml\Refactor\EntityWriter\ProductXmlWriter;
use DemandwareXml\Refactor\Xml\XmlWriter;
use InvalidArgumentException;

class Product implements WriteableEntityInteface
{
    public $id;
    public $upc;
    public $minOrderQuantity          = 1;
    public $stepQuantity              = 1;
    public $displayName;
    public $longDescription;
    public $onlineFlag;
    public $onlineFrom;
    public $onlineTo;
    public $availableFlag;
    public $searchableFlag;
    public $searchableIfUnavailableFlag;
    public $tax;
    public $images                    = [];
    public $imageViewType;
    public $brand;
    public $searchRank;
    public $sitemapIncludedFlag;
    public $sitemapChangeFrequency;
    public $sitemapPriority;
    public $pageAttributes            = [];
    public $customAttributes          = [];
    public $sharedVariationAttributes = [];
    public $variants                  = [];
    public $classificationCategoryId;
    public $classificationCatalogId;

    public function __construct(string $id)
    {
        $this->id = $id;
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

    public function setOnlineFromTo(?DateTimeInterface $from, ?DateTimeInterface $to): void
    {
        if ($from !== null) {
            $this->onlineFrom = $from;
        }

        if ($to !== null) {
            $this->onlineTo = $to;
        }
    }

    public function setSearchableFlags(bool $searchableFlag, bool $availableFlag, bool $searchableIfUnavailableFlag): void
    {
        $this->searchableFlag              = $searchableFlag;
        $this->availableFlag               = $availableFlag;
        $this->searchableIfUnavailableFlag = $searchableIfUnavailableFlag;
    }

    public function setTax(?float $tax): void
    {
        if ($tax === null) {
            return;
        }

        if ($tax == 0) {
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

    public function setSitemap(?float $sitemapPriority = null, bool $sitemapIncludedFlag = true, string $sitemapChangeFrequency = 'weekly'): void
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

    public function setPageAttributes(?string $pageTitle = null, ?string $pageDescription = null, ?string $pageKeywords = null, ?string $pageUrl = null): void
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

    public function setSharedVariationAttributes(array $sharedVariationAttributes = []): void
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

    public function setClassificationCategory(string $classificationCategoryId, string $classificationCatalogId): void
    {
        $this->classificationCategoryId = $classificationCategoryId;
        $this->classificationCatalogId  = $classificationCatalogId;
    }

    public function write(XmlWriter $writer): void
    {
        (new ProductXmlWriter($writer, $this))->write();
    }
}
