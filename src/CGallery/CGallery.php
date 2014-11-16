<?php
class CGallery{
	private $galleryDir;
	private $galleryBase;
	private $path = null;
	
	private $pathToGallery;
	
	public function __construct($galleryDir, $galleryBase = ''){
		$this->galleryDir = $galleryDir;
		$this->galleryBase = $galleryBase;
	}
	
	public function CreateGallery(){
		$gallery = null;
	
		if(!isset($this->path)){
			$this->path = "";
		}
		
		$this->pathToGallery = realpath($this->galleryDir .DIRECTORY_SEPARATOR .$this->path);
		
		$this->Validate();
		
		if(is_dir($this->pathToGallery)){
			$gallery = $this->ReadAllItemsInDir($this->pathToGallery);
		}else if(is_file($this->pathToGallery)){
			$gallery = $this->ReadItem($this->pathToGallery);
		}
		
		$breadcrumb = $this->CreateBreadcrumb($this->pathToGallery);
		
		$output = array($breadcrumb,$gallery);
		
		return $output;
	}
	
	function ReadAllItemsInDir($path, $validImages = array('png', 'jpg', 'jpeg')){
		$files = glob($path .'/*');
		$gallery = "<ul class='gallery'>\n";
		$len = strlen($this->galleryDir);
		
		foreach($files as $file){
			$parts = pathinfo($file);
			$href = str_replace('\\', '/', substr($file, $len +1));
			
			//Is this an image or a directory?
			if(is_file($file) && in_array($parts['extension'], $validImages)){
				$item = "<img src='img.php?src="
					.$this->galleryBase
					.$href
					."&amp;width=128&amp;height=128&amp;crop-to-fit' alt='' />";
				$caption = basename($file);
			}else if(is_dir($file)){
				$item = "<img src='img/folder.png' alt='' />";
				$caption = basename($file) .'/';
			}else{
				continue;
			}
			
			//Avoid to long captions breaking layout
			$fullCaption = $caption;
			if(strlen($caption) > 18){
				$caption = substr($caption, 0, 10) . '...' .substr($caption, -5);
			}
			
			$gallery .= "<li><a href='?path={$href}' title='{$fullCaption}'><figure class='figure overview'>{$item}<figcaption>{$caption}</figcaption></figure></a></li>\n";
		}
		$gallery .= "</ul>\n";
		
		
		return $gallery;
	}

	function ReadItem($path, $validImages = array('png', 'jpg', 'jpeg')){
		$parts = pathinfo($path);
		if(!(is_file($path) && in_array($parts['extension'], $validImages))){
			return "<p>This is not a valid image for this gallery.</p>";
		}
		
		//Get info on image
		$imgInfo = list($width, $height, $type, $attr) = getimagesize($path);
		$mime = $imgInfo['mime'];
		$gmdate = gmdate("D, d M Y H:i:s", filemtime($path));
		$filesize = round(filesize($path) /1024);
		
		//Get constraints to display original
		$displayWidth = $width > 800 ? "&amp;width=800" :null;
		$displayHeight = $height > 600 ? "&amp;height=600" : null;
		
		//Display details on image
		$len = strlen($this->galleryDir);
		$href = $this->galleryBase .str_replace('\\', '/', substr($path, $len +1));
		$item = <<<EOD
			<p><img src='img.php?src={$href}{$displayWidth}{$displayHeight}' alt=''/></p>
			<p>Original image dimensions are {$width}x{$height} pixels. <a href='img.php?src={$href}'>View original image</a>.</p>
			<p>File size is {$filesize}KBytes.</p>
			<p>Image has mimetype: {$mime}.</p>
			<p>Image was last modified: {$gmdate} GMT.</p>
EOD;
		return $item;
	}

	function CreateBreadcrumb($path){
		$parts = explode('/', trim(substr($path, strlen($this->galleryDir) +1), '/'));
		$breadcrumb = "<ul class='breadcrumb'>\n<li><a href='?'>Hem</a> » </li>\n";
		
		if(!empty($parts[0])){
			$combine = null;
			foreach($parts as $part){
				$combine .=($combine ? '/' :null) .$part;
				$breadcrumb .= "<li><a href='?path={$combine}'>$part</a> » </li>\n";
			}
		}
		
		$breadcrumb .= "</ul>\n";
		return $breadcrumb;
	}	

	
	
	private function Validate(){
		is_dir($this->galleryDir) or $this->errorMessage('You are trying to use an invalid directory.');
		substr_compare($this->galleryDir, $this->pathToGallery, 0, strlen($this->galleryDir)) == 0 or $this->errorMessage('Security constraint: Source gallery is not directly below the directory GALLERY_PATH.');	
	}

	private function errorMessage($message) {
	  header("Status: 404 Not Found");
	  die('gallery.php says 404 - ' . htmlentities($message));
	}
	
	public function setPath($path){
		$this->path = $path;
	}

}