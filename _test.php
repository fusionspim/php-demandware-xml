<?php
require_once __DIR__ . '/vendor/autoload.php';

use DemandwareXml\Refactor\Entity\{CustomAttribute, Product};
use DemandwareXml\Refactor\Serializer\{CustomAttributeSerializer, ProductSerializer};
use DemandwareXml\Refactor\Xml\Writer;

$product                         = new Product();
$product->id                     = 'GEM665896';
$product->upc                    = '5057796043570';
$product->minOrderQuantity       = 1;
$product->stepQuantity           = 1;
$product->displayName            = 'Pink Polka Dot Wellington Boots';
$product->longDescription        = 'If they’re simply dotty about the great outdoors, then these welly boots are an essential to help them stay warm and dry. The brightly-coloured spot print makes it fun to brave even the worst of British weather.<br /> • Fully lined<br />Fabric composition: Upper: other materials<br />';
$product->onlineFlag             = true;
$product->onlineFrom             = new DateTime('2018-01-01 10:10:10');
$product->onlineTo               = null;
$product->searchableFlag         = false;
$product->images                 = ['5057796043570'];
$product->brand                  = 'George';
$product->searchRank             = 1;
$product->sitemapIncludedFlag    = true;
$product->sitemapChangeFrequency = 'weekly';
$product->sitemapPriority        = 1.0;
$product->pageTitle              = 'Amazing Product';
$product->pageDescription        = 'Buy our Product today!';
$product->pageKeywords           = 'Product, test, example';
$product->pageUrl                = 'http://example.com/product/123';
$product->classificationCategory = 'CAT123';
$product->defaultVariant         = 'SKU0000003';

$product->customAttributes = [
    new CustomAttribute('boolFalse', false),
    new CustomAttribute('boolTrue', true),
    new CustomAttribute('multiWow', ['so', 'such', 'many', 'much', 'very']),
    new CustomAttribute('primaryImage', 'product-123.png'),
    new CustomAttribute('type', 'Examples'),
    new CustomAttribute('zzz', 'Should be exported last within custom-attributes'),
];

$product->sharedVariationAttributes = [
    'colour',
    'size',
    'AT0001',
    'AT0002',
];

$product->variants = [
    'SKU0000001',
    'SKU0000002',
    'SKU0000003',
];

// Build XML.
$file = __DIR__ . '/_test.xml';

$writer = new Writer();
$writer->entityMap = [
    Product::class         => ProductSerializer::class,
    CustomAttribute::class => CustomAttributeSerializer::class,
];
//$writer->openMemory();
$writer->openFile($file);
$writer->setIndent(true);
$writer->startCatalog('GeorgeCatalog');

for ($i = 1; $i <= 100000; $i++) {
    $writer->writeEntity($product);

    if ($i % 10000 === 0) {
        echo 'Flushing ' . $i . "\n";
        $writer->flush(true);
    }
}

$writer->endCatalog();
//echo $writer->outputMemory(true);
// echo file_get_contents($file);

// Demandware Product XML
/*
  <product product-id="PRODUCT123">
    <upc>500000000000</upc>
    <min-order-quantity>1</min-order-quantity>
    <step-quantity>1</step-quantity>
    <display-name xml:lang="x-default">Product number 123</display-name>
    <long-description xml:lang="x-default">&lt;b&gt;Product&lt;/b&gt; The description for an &lt;i&gt;example&lt;/i&gt; product! • Bullet Point</long-description>
    <online-flag>true</online-flag>
    <online-from>2015-01-23T01:23:45</online-from>
    <online-to>2025-01-23T01:23:45</online-to>
    <searchable-flag>false</searchable-flag>
    <images>
      <image-group view-type="large">
        <image path="product-123.png"/>
      </image-group>
    </images>
    <brand>SampleBrand™</brand>
    <search-rank>1</search-rank>
    <sitemap-included-flag>true</sitemap-included-flag>
    <sitemap-changefrequency>weekly</sitemap-changefrequency>
    <sitemap-priority>1.0</sitemap-priority>
    <page-attributes>
      <page-title xml:lang="x-default">Amazing Product</page-title>
      <page-description xml:lang="x-default">Buy our Product today!</page-description>
      <page-keywords xml:lang="x-default">Product, test, example</page-keywords>
      <page-url xml:lang="x-default">http://example.com/product/123</page-url>
    </page-attributes>
    <custom-attributes>
      <custom-attribute attribute-id="boolFalse">false</custom-attribute>
      <custom-attribute attribute-id="boolTrue">true</custom-attribute>
      <custom-attribute attribute-id="multiWow">
        <value>so</value>
        <value>such</value>
        <value>many</value>
        <value>much</value>
        <value>very</value>
      </custom-attribute>
      <custom-attribute attribute-id="primaryImage">product-123.png</custom-attribute>
      <custom-attribute attribute-id="type">Examples</custom-attribute>
      <custom-attribute attribute-id="zzz">Should be exported last within custom-attributes</custom-attribute>
    </custom-attributes>
    <variations>
      <attributes>
        <shared-variation-attribute variation-attribute-id="AT001" attribute-id="AT001"/>
        <shared-variation-attribute variation-attribute-id="AT002" attribute-id="AT002"/>
      </attributes>
      <variants>
        <variant product-id="SKU0000001"/>
        <variant product-id="SKU0000002"/>
        <variant product-id="SKU0000003" default="true"/>
      </variants>
    </variations>
    <classification-category catalog-id="TestCatalog">CAT123</classification-category>
  </product>
 */
