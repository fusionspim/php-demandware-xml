<?php
namespace DemandwareXml\Refactor\Entity;

class Product implements EntityInterface
{
    public $id;
    public $upc;
    public $minOrderQuantity;
    public $stepQuantity;
    public $displayName;
    public $longDescription;
    public $onlineFlag;
    public $onlineFrom;
    public $onlineTo;
    public $availableFlag;
    public $searchableFlag;
    public $searchableIfUnavailableFlag;
    public $tax;
    public $images = [];
    public $brand;
    public $searchRank;
    public $sitemapIncludedFlag;
    public $sitemapChangeFrequency;
    public $sitemapPriority;
    public $pageTitle;
    public $pageDescription;
    public $pageKeywords;
    public $pageUrl;
    public $customAttributes = [];
    public $sharedVariationAttributes = [];
    public $variants = [];
    public $defaultVariant;
    public $classificationCategoryId;
    public $classificationCatalogId;
}
