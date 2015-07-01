<?php
use \FusionsPIM\DemandwareXml\XmlDocument;
use \FusionsPIM\DemandwareXml\XmlProduct;

$document = new XmlDocument('TestCatalog');

foreach (['Product', 'Set', 'Bundle'] as $index => $example) {
    $element = new XmlProduct(strtoupper($example) . '123');
    $element->setName($example . ' number 123');
    $element->setDescription('The description for an example ' . strtolower($example) . '!');
    $element->setUpc('50000000000' . $index);
    $element->setQuantities(); // include, but use defaults
    $element->setRank(1);
    $element->setSitemap(); // include, but use defaults
    $element->setBrand('SampleBrand™');
    $element->setFlags(true, false);
    $element->setDates('2015-01-23 01:23:45', '2025-01-23 01:23:45');
    $element->setClassification('CAT123', 'TestCatalog');
    $element->setSharedAttributes(['AT001', 'AT002']);
    $element->setVariants(['SKU0000001' => false, 'SKU0000002' => false, 'SKU0000003' => true]);

    $element->setPageAttributes(
        'Amazing ' . $example,
        'Buy our ' . $example . ' today!',
        $example . ', test, example',
        'http://example.com/' . strtolower($example) . '/123'
    );

    $element->setCustomAttributes([
        'type'         => 'Examples',
        'primaryImage' => strtolower($example) . '-123.png',
        'ageRestrict'  => 'Over 18',
        'department'   => 'PHP'
    ]);

    if ('Bundle' === $type) {
        $element->setProductQuantities(['SKU0000001' => 10, 'SKU0000002' => 20]);
        $element->setTax(20);
    } elseif ('Set' === $type) {
        $element->setProducts(['PRODUCT123', 'PRODUCT456']);
    }

    $document->addObject($element);
}

$document->save('products.xml');
