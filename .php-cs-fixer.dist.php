<?php
$config = FusionsPim\PhpCsFixer\Factory::fromDefaults([
    'date_time_immutable' => false, // Needed for tests
    'group_import'        => false, // Currently broken for root classes
]);

return $config->setFinder(
    $config->getFinder()
        ->notName('rector.php')
);
