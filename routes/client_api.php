<?php

Route::resources([
    'orders' => 'Api\OrderController',
    'items' => 'Api\ItemController',
    'lenspackages' => 'Api\LensPackageController',
], ['only' => ['index', 'show', 'update']]);

Route::resource('itemstation', 'Api\ItemStationController', ['only' => [
    'store'
]]);
