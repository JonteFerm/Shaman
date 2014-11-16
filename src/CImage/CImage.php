<?php
class CImage{
	const MAX_WIDTH = 2000;
	const MAX_HEIGHT = 2000;
	
	//Paths
	private $src = null;
	private $imgDir;
	private $cacheDir;
	private $pathToImage = null;
	
	//Original image info
	private $width;
	private $height; 
	private $type;
	private $attr;
	
	//New image info and settings
	private $newWidth = null;
	private $newHeight = null;
	private $verbose = null;
	private $saveAs = null;
	private $quality = 60;
	private $ignoreCache = null;
	private $cropToFit = null;
	private $sharpen = null;
	private $cropWidth = null;
	private $cropHeight = null;
	private $cacheFileName=null;
	
	private $verboseLog;
	
	public function __construct($imgDir,$cacheDir){
		$this->imgDir = $imgDir;
		$this->cacheDir = $cacheDir;
	}
	
	public function DisplayImage(){
		$this->pathToImage = realpath($this->imgDir .$this->src);
		$this->GenerateImageInfo();
		$original = $this->OpenOrigin();
		$this->Validate();
		$this->CalculateDimensions();
		$this->CreateCacheFileName();
		$newImage = $this->ProcessImage($original);	
		$this->ControlCache();
		$this->SaveImage($newImage);
		$this->OutputImage($this->cacheFileName);
	}
		
	private function GenerateImageInfo(){
		$imgInfo = list($this->width, $this->height, $this->type, $this->attr) = getimagesize($this->pathToImage);
		!empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
		$mime = $imgInfo['mime'];
	}
	
	private function OpenOrigin(){
		$parts = pathinfo($this->pathToImage);
		$fileExtension = $parts['extension'];
		
		switch($fileExtension){
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($this->pathToImage);
				break;
			case 'png':
				$image = imagecreatefrompng($this->pathToImage);
				break;
		}
		
		return $image;
	}
	
	private function CalculateDimensions(){
		$aspectRatio = $this->width/$this->height;
		if($this->cropToFit && $this->newWidth && $this->newHeight){
			$targetRatio = $this->newWidth/$this->newHeight;
			$this->cropWidth = $targetRatio > $aspectRatio ? $this->width : round($this->height * $targetRatio);
			$this->cropHeight = $targetRatio > $aspectRatio ? $this->height : round($this->width / $targetRatio);
			if($this->verbose) {$this->Verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}."); }
		}else if($this->newWidth && !$this->newHeight){
			$this->newHeight = round($this->newWidth/$aspectRatio);
			if($this->verbose){$this->Verbose("New width is known {$newWidth}, height is calculated to {$newHeight}.");}
		}else if(!$this->newWidth  && $this->newHeight){
			$this->newWidth = round($this->newHeight * $aspectRatio);
			if($this->verbose){$this->Verbose("New height is known {$newHeight}, width is calculated to {$newWidth}.");}
		}else if($this->newWidth && $this->newHeight){
			$ratioWidth = $this->width/$this->newWidth;
			$ratioHeight = $this->height/$this->newHeight;
			$ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
			$this->newWidth = round($this->width/$ratio);
			$this->newHeight = round($this->height/$ratio);
			if($this->verbose) {$this->Verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}."); }
		}else{
			$this->newWidth = $this->width;
			$this->newHeight = $this->height;
			if($this->verbose) {$this->Verbose("Keeping original width & heigth."); }
		}
	}
		
	private function CreateCacheFileName(){
		$parts = pathinfo($this->pathToImage);
		$fileExtension = $parts['extension'];
		$this->saveAs = is_null($this->saveAs) ? $fileExtension : $this->saveAs;
		$quality_ = is_null($this->quality) ? null : "_q{$this->quality}";
		$dirName = preg_replace('/\//', '-', dirname($this->src));
		$cropToFit_ = is_null($this->cropToFit) ? null : "_cf";
		$sharpen_ = is_null($this->sharpen) ? null : "_s";
		$cacheFileName = $this->cacheDir ."-{$dirName}-{$parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}.{$this->saveAs}";
		$this->cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);	
	}
	
	private function ProcessImage($image){
		if($this->cropToFit){
			if($this->verbose){$this->Verbose("Resizing, crop to fit");}
			$cropX = round(($this->width - $this->cropWidth)/2);
			$cropY = round(($this->height - $this->cropHeight)/2);
			$imageRezised = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
			imagecopyresampled($imageRezised, $image, 0, 0, $cropX, $cropY, $this->newWidth, $this->newHeight, $this->cropWidth, $this->cropHeight);
			$image = $imageRezised;
			$this->width = $this->newWidth;
			$this->height = $this->newHeight;
		}else if(!($this->newWidth == $this->width && $this->newHeight == $this->height)){
			$imageRezised = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
			imagecopyresampled($imageRezised, $image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
			$image = $imageRezised;
			$this->width = $this->newWidth;
			$this->height = $this->newHeight;
		}

		if($this->sharpen){
			$image = SharpenImage($image);
		}
		
		return $image;
	}
	
	private function createImageKeepTransparency($width, $height){
		$img = imagecreatetruecolor($width, $height);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		return $img;
	}


	private function SharpenImage($image){
		$matrix = array(
			array(-1,-1,-1),
			array(-1,16,-1),
			array(-1,-1,-1)
		);
		
		$divisor = 8;
		$offset = 0;
		imageconvolution($image, $matrix, $divisor, $offset);
		return $image;
	
	}
	
	private function ControlCache(){
		$imageModifiedTime = filemtime($this->pathToImage);
		$cacheModifiedTime = is_file($this->cacheFileName) ? filemtime($this->cacheFileName) : null;
		
		if(!$this->ignoreCache && is_file($this->cacheFileName) && $imageModifiedTime < $cacheModifiedTime){
			if($this->verbose){$this->Verbose("Cache file is valid, output it.");}
			$this->OutputImage($this->cacheFileName);
		}
	}
	
	private function SaveImage($newImage){
		switch($this->saveAs){
			case 'jpg':
			case 'jpeg':
				imagejpeg($newImage, $this->cacheFileName, $this->quality);
				break;
			case 'png':
				imagealphablending($newImage, false);
				imagesavealpha($newImage, true);
				imagepng($newImage, $this->cacheFileName);
				break;
			default:
				$this->errorMessage('No support to save as this file extension.');
				break;
		}
	}
	

	
	private function OutputImage($file){
		$info = getimagesize($file);
		!empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
		$mime = $info['mime'];
		$lastModified = filemtime($file);
		$gmdate = gmdate("D, d M Y H:i:s", $lastModified);
		
		if($this->verbose){
			$this->Verbose("Momory peak: " .round(memory_get_peak_usage() /1024/1024) ."M");
			$this->Verbose("Memory limit: " .ini_get('memory_limit'));
			$this->Verbose("Time is {$gmdate} GMT.");
		}
		
		if(!$this->verbose){
			header('Last-Modified: ' .$gmdate .' GMT');
		}
		
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
			if($this->verbose){
				$this->Verbose("Would send header 304 Not Modified, but its verbose mode."); 
				exit;
			}
			header('HTTP/1.0 304 Not Modified');
		}else{
			if($this->verbose){
				$this->Verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode.");
				exit;
			}
			header('Content-type: ' .$mime);
			readfile($file);
		}
		exit;
	}
	
	private function Validate(){
		is_dir($this->imgDir) or $this->errorMessage('The image dir is not a valid directory.');
        is_writable($this->cacheDir) or $this->errorMessage('The cache dir is not a writable directory.'); 
		is_null($this->saveAs) or in_array($this->saveAs, array('png', 'jpg', 'jpeg')) or $this->errorMessage('Not a valid extension.');
		is_null($this->quality) or (is_numeric($this->quality) and $this->quality > 0 and $this->quality <=100) or $this->errorMessage('Quality out of range.');
		is_null($this->cropToFit) or ($this->cropToFit and $this->newWidth and  $this->newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
		is_null($this->newWidth) or (is_numeric($this->newWidth) and $this->newWidth > 0 and $this->newWidth <= self::MAX_WIDTH) or $this->errorMessage('Width is invalid.');
        is_null($this->newHeight) or (is_numeric($this->newHeight) and $this->newHeight > 0 and $this->newHeight <= self::MAX_HEIGHT) or $this->errorMessage('Height is invalid.');
         
	}
	
	function errorMessage($message) {
		header("Status: 404 Not Found");
		die('img.php says 404 - ' . htmlentities($message));
	}

	private function Verbose($message) {
		echo "<p>" . htmlentities($message) . "</p>";
	}
	
	private function OutputVerboseLog(){
	
	}
	
	public function setSrc($src){
		$this->src = $src;
	}
	
	public function setVerbose($verbose){
		$this->verbose = $verbose;
	}
	
	public function setSaveAs($saveAs){
		$this->saveAs = $saveAs;
	}
	
	public function setQuality($quality){
		$this->quality = $quality;
	}
	
	public function setIgnoreCache($ignoreCache){
		$this->ignoreCache = $ignoreCache;
	}
	
	public function setNewWidth($newWidth){
		$this->newWidth = $newWidth;
	}
	
	public function setNewHeight($newHeight){
		$this->newHeight = $newHeight;
	}
	
	public function setCropToFit($cropToFit){
		$this->cropToFit = $cropToFit;
	}
	
	public function setSharpen($sharpen){
		$this->sharpen = $sharpen;
	}

}