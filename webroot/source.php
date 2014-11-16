<?php 

include(__DIR__.'/config.php'); 

 
$source = new CSource(array('secure_dir' => '..', 'base_dir' => '..'));

 
$shaman['stylesheets'][] = 'css/source.css';
 
$shaman['title'] = "Jonathan Ferm - Redovisningar";
 
$shaman['main'] = "<article><h1>Visa Källkod</h1><p>". $source->View()."</p></article>";


include(SHAMAN_THEME_PATH);