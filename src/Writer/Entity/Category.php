<?php

namespace DemandwareXml\Writer\Entity;

use DateTimeInterface;
use DemandwareXml\Writer\EntityWriter\CategoryXmlWriter;
use DemandwareXml\Writer\Xml\XmlWriter;
use InvalidArgumentException;

class Category implements WriteableEntityInteface
{
    public ?string $displayName            = null;
    public ?bool $onlineFlag               = null;
    public ?DateTimeInterface $onlineFrom  = null;
    public ?DateTimeInterface $onlineTo    = null;
    public ?string $parentId               = null;
    public ?string $template               = null;
    public ?bool $sitemapIncludedFlag      = null;
    public ?string $sitemapChangeFrequency = null;
    public ?string $sitemapPriority        = null;
    public array $pageAttributes           = [];
    public array $customAttributes         = [];

    public function __construct(public string $id) {}

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
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

    public function setParent(string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
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

    public function addCustomAttributes(array $map): void
    {
        foreach ($map as $id => $value) {
            $this->addCustomAttribute(new CustomAttribute($id, $value));
        }
    }

    public function write(XmlWriter $writer): void
    {
        (new CategoryXmlWriter($writer, $this))->write();
    }
}
