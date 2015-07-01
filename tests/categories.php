<?php
use \FusionsPIM\DemandwareXml\XmlAssignment;
use \FusionsPIM\DemandwareXml\XmlCategory;
use \FusionsPIM\DemandwareXml\XmlDocument;

$document = new XmlDocument('TestCatalog');

foreach (['Socks', 'Death Stars', 'Donuts'] as $index => $example) {
    $element = new XmlCategory('CAT' . $index);
    $element->setName($example);
    $element->setParent('CAT0');
    $element->setTemplate('cat-listings.html');
    $element->setFlags(true);
    $element->setSitemap(3);
    $element->setPageAttributes($example, 'Buy ' . $example, strtolower($example), '/' . $example);

    $element->setCustomAttributes([
        'itemsPerPage' => 30,
        'promoMast'    => 'cat' . $index . '-banner.png',
        'hasOffers'    => true
    ]);

    $document->addObject($element);
}

$document->save('categories.xml');

// categories are done, lets create some assignments!

$document = new XmlDocument('TestCatalog');

foreach (['PROD1' => 'CAT1', 'PROD1' => 'CAT2', 'PROD2' => 'CAT1', 'PROD3' => 'CAT3'] as $product => $category) {
    // simulate some application logic
    $primary = ('PROD1' === $product && 'CAT2' === $category);
    $deleted = ('PROD2' === $product && 'CAT1' === $category);

    $element = new XmlAssignment($product, $category);
    $element->setPrimary($primary);

    if ($deleted) {
        $element->setDeleted();
    }

    $document->addObject($element);

    // simulate some more application logic, put all primary products in CAT42
    if ($primary) {
        $element = new XmlAssignment($product, 'CAT42');

        $document->addObject($element);
    }
}

$document->save('assignments.xml');
