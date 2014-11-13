<?php
/**
 * Config-file for Anax. Change settings here to affect installation.
 *
 */
 
/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly
 
 
/**
 * Define Shaman paths.
 *
 */
define('SHAMAN_INSTALL_PATH', __DIR__ . '/..');
define('SHAMAN_THEME_PATH', SHAMAN_INSTALL_PATH . '/theme/render.php');


/**
* Image paths
*
*/
define('IMG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', __DIR__ . DIRECTORY_SEPARATOR .'cache' .DIRECTORY_SEPARATOR);
define('GALLERY_PATH', __DIR__ .DIRECTORY_SEPARATOR .'img' .DIRECTORY_SEPARATOR .'gallery');
define('GALLERY_BASE', 'gallery/');
 
/**
 * Include bootstrapping functions.
 *
 */
include(SHAMAN_INSTALL_PATH . '/src/bootstrap.php');

 
 
/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();
 
 
/**
 * Create the Shaman variable.
 *
 */
$shaman = array();
 
 
/**
 * Site wide settings.
 *
 */
$shaman['lang']         = 'sv';
$shaman['title_append'] = ' | Shaman en webbtemplate';

/**
 * Theme related settings.
 *
 */
$shaman['stylesheet'] = 'css/style.css';
$shaman['favicon']    = 'favicon.ico';

$menu = array(
  'class' => 'standard',
  'items' => array(
  'home'  => array('text'=>'HEM',  'url'=>'hello.php', 'title'=>'Hello'),
  'source' => array('text'=>'KÄLLKOD', 'url'=>'source.php',  'title'=>'Se källkoden'), ),
  'callback_selected' => function($url) {
    if(basename($_SERVER['SCRIPT_FILENAME']) == $url) {
      return true;
    }
  }
); 

$shaman['header'] = <<<EOD
<img class='sitelogo' src='img/shaman.png' alt='Shaman Logo'/>
<span class='sitetitle'>Shaman webbtemplate</span>
<span class='siteslogan'>Återanvändbara moduler för webbutveckling med PHP</span>
EOD;

$shaman['footer'] = <<<EOD
<footer><span class='sitefooter'>Copyright (c) Jonathan Ferm 2014 | <a href='https://github.com/JonteFerm'> på GitHub</a> | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a></span></footer>
EOD;
 
$shaman['database']['dsn'] = 'mysql:host=127.0.0.1;dbname=web_content;';
$shaman['database']['username'] = 'root';
$shaman['database']['password'] = '';
$shaman['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");

/*$shaman['database']['dsn'] = 'mysql:host=blu-ray.student.bth.se;dbname=jofe14;';
$shaman['database']['username'] = 'jofe14';
$shaman['database']['password'] = 'Fc%*4Iu#';
$shaman['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");*/

