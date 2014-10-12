<?php 

include(__DIR__.'/config.php'); 
 
 
$shaman['title'] = "Hello World";
 
$shaman['header'] = <<<EOD
<img class='sitelogo' src='img/shaman.png' alt='Shaman Logo'/>
<span class='sitetitle'>Shaman webbtemplate</span>
<span class='siteslogan'>återanvändbara moduler för webbutveckling med PHP</span>
EOD;
 
$shaman['main'] = <<<EOD
<h1>Hej Världen</h1>
<p>Detta är en exempelsida.</p>
EOD;
 
$shaman['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright (c) Jonathan Ferm  | <a href=''> på GitHub</a> | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;
 
 
include(SHAMAN_THEME_PATH);