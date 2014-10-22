<?php 

include(__DIR__.'/config.php'); 
  
$shaman['title'] = "Hello World";
 
$shaman['main'] = <<<EOD
<h1>Hej Världen</h1>
<p>Detta är en exempelsida.</p>
EOD;
 
 
include(SHAMAN_THEME_PATH);