<?php
$dir = __DIR__ . '/../../avatec/opencart/';

copy(
    __DIR__ . '/admin/controller/extension/shipping/inpost.php',
    $dir . 'admin/controller/extension/shipping/inpost.php'
);

copy(
    __DIR__ . '/admin/language/pl-PL/extension/shipping/inpost.php',
    $dir . 'admin/language/pl-PL/extension/shipping/inpost.php'
);

copy(
    __DIR__ . '/admin/view/template/extension/shipping/inpost.twig',
    $dir . 'admin/view/template/extension/shipping/inpost.twig'
);

copy(
    __DIR__ . '/catalog/language/pl-PL/extension/shipping/inpost.php',
    $dir . 'catalog/language/pl-PL/extension/shipping/inpost.php'
);

copy(
    __DIR__ . '/catalog/model/extension/shipping/inpost.php',
    $dir . 'catalog/model/extension/shipping/inpost.php'
);

copy(
    __DIR__ . '/catalog/controller/api/inpost.php',
    $dir . 'catalog/controller/api/inpost.php'
);

copy(
    __DIR__ . '/catalog/view/javascript/inpost.min.js',
    $dir . 'catalog/view/javascript/inpost.min.js'
);

copy(
    __DIR__ . '/catalog/view/javascript/inpost.js',
    $dir . 'catalog/view/javascript/inpost.js'
);
