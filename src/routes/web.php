<?php

Route::get('/contact', function() {
    $pdf =  new \Sparkosis\Pdf\Pdf();
    $pdf->addAssets('style.css', public_path('css/app.css'));
    return $pdf->generateFromView('welcome')->get();


});
