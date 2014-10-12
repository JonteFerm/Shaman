<?php 

include(__DIR__.'/config.php'); 
 
$shaman['title'] = "404";
$shaman['header'] = "";
$shaman['main'] = "This is a Shaman 404. Document is not here.";
$shaman['footer'] = "";
 
header("HTTP/1.0 404 Not Found");
 
 
include(SHAMAN_THEME_PATH);