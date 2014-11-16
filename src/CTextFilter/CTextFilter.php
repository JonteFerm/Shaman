<?php
class CTextFilter{
	//Helper, BBCode formatting converting to HTML.
	private function bbcode2html($text) {
	  $search = array( 
		'/\[b\](.*?)\[\/b\]/is', 
		'/\[i\](.*?)\[\/i\]/is', 
		'/\[u\](.*?)\[\/u\]/is', 
		'/\[img\](https?.*?)\[\/img\]/is', 
		'/\[url\](https?.*?)\[\/url\]/is', 
		'/\[url=(https?.*?)\](.*?)\[\/url\]/is' 
		);   
	  $replace = array( 
		'<strong>$1</strong>', 
		'<em>$1</em>', 
		'<u>$1</u>', 
		'<img src="$1" />', 
		'<a href="$1">$1</a>', 
		'<a href="$1">$2</a>' 
		);     
	  return preg_replace($search, $replace, $text);
	}


	//Make clickable links from URLs in text.
	private function make_clickable($text) {
	  return preg_replace_callback(
		'#\b(?<![href|src]=[\'"])https?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
		create_function(
		  '$matches',
		  'return "<a href=\'{$matches[0]}\'>{$matches[0]}</a>";'
		),
		$text
	  );
	}


	

	public function doFilter($text, $filter) {
	  // Define all valid filters with their callback function.
	  $valid = array(
		'bbcode'   => 'bbcode2html',
		'link'     => 'make_clickable',
		'markdown' => 'markdown',
		'nl2br'    => 'nl2br',  
	  );

	  // Make an array of the comma separated string $filter
	  $filters = preg_replace('/\s/', '', explode(',', $filter));

	  // For each filter, call its function with the $text as parameter.
	  foreach($filters as $func) {
		if(isset($valid[$func])) {
		  $text = self::$valid[$func]($text);
		} 
		else {
		  throw new Exception("The filter '$filter' is not a valid filter string.");
		}
	  }

	  return $text;
	}
	
	
	//use \Michelf\MarkdownExtra;
	//Format text according to Markdown syntax.
	private function markdown($text) {
	  require_once(__DIR__ . '/php-markdown/Michelf/Markdown.inc.php');
	  require_once(__DIR__ . '/php-markdown/Michelf/MarkdownExtra.inc.php');
	  return \Michelf\MarkdownExtra::defaultTransform($text);
	}
	
	public function nl2br($text) {
        $text = str_replace('\n', "\n", $text);
        return nl2br($text);
    } 

}