<?php
//Author: Kevin Jones
//Email: kevin.d.jones@gmail.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 06/11/2009
//Copyright: Kevin Jones 2009
//License: ./documentation/License.txt
//
//Note: You should find no need to edit this file
//
//Configuration Parameters are set in configuration.php
//Please read over the documents in the documentation folder
?>

<div id="weblery-content">

<?php
//Get Weblery's Configuration
include 'configuration.php';

//Instantiate the gallery
$weblery = new weblery();
?>
<link type="text/css" href="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jqueryUI/css/smoothness/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="<?php echo $weblery->__get('webleryBasePath'); ?>assets/css/weblery.css" />
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jqueryUI/js/jquery-ui-1.7.1.custom.min.js"></script>

<?php
//Weblery Class Definition
class weblery {
	protected static $webleryBasePath = '';
	protected static $galleryBasePath = '';
	protected static $initGalleryBasePath = '';
	protected static $imgBasePath = '';
	protected static $stillInitializing = false;
	protected static $baseStartPage = '';
	protected static $albumCachePath = '';
	protected static $selectedAlbumCachePath = '';
	public $start = 0;
	public $selectedAlbum = '';

	function __construct() {
		self::__set('webleryBasePath',confWebleryBasePath); //Directory where weblery.php is located
		self::__set('galleryBasePath',self::__get('webleryBasePath') . confGalleryBasePath); //Directory where weblery albums are located
		if (baseName($_SERVER['SCRIPT_NAME']) == 'weblery.php') {
			self::__set('initGalleryBasePath',confGalleryBasePath); //Directory where weblery.php is located
			self::__set('albumCachePath','assets/album_cache/'); //Directory where weblery album images are created and stored
		} else {
			self::__set('initGalleryBasePath',self::__get('galleryBasePath')); //Directory where weblery.php is located
			self::__set('albumCachePath',self::__get('webleryBasePath') . 'assets/album_cache/'); //Directory where weblery album images are created and stored
		}
		self::__set('imgBasePath',self::__get('webleryBasePath') . 'assets/img/'); //Directory where weblery images is located
		self::__set('layoutFile',self::__get('webleryBasePath') . 'assets/layout/'.confLayoutFile);
		self::__set('baseStartPage',confBaseStartPage);
		self::__set('defaultThumbWidth',confDefaultThumbWidth);
		self::__set('defaultThumbHeight',confDefaultThumbHeight);
		self::__set('mainImageSize',confMainImageSize);
		
		$tempAlbumArray = self::getAlbumArray();
		if (count($tempAlbumArray)) {
			if (isset($_GET['selectedAlbum']) && in_array($_GET['selectedAlbum'],$tempAlbumArray) && strlen($_GET['selectedAlbum']) > 0) {
				self::__set('selectedAlbum',$_GET['selectedAlbum']);
			} else {
				self::__set('selectedAlbum',$tempAlbumArray[0]);
			}
			if (isset($_GET['start']) && strlen($_GET['start']) > 0) {
				self::__set('start',$_GET['start']);
			} else {
				self::__set('start',0);
			}
			self::__set('selectedAlbumPath',self::__get('initGalleryBasePath').self::__get('selectedAlbum')."/");
			self::__set('selectedAlbumCachePath',self::__get('albumCachePath').self::__get('selectedAlbum')."/");
			
			self::cleanAlbumCache();
			
			if (self::isInitialized()) {
				self::__set('stillInitializing',false);
			} else {
				self::__set('stillInitializing',true);
				if (isset($_GET['step']) && strlen($_GET['step']) > 0 && is_numeric($_GET['step'])) {
					self::__set('initStep',$_GET['step']);
				} else {
					self::__set('initStep',0);
				}
				self::initializeAlbum(self::__get('initStep'));
			}
		} else {
			echo "No Albums detected in the Album directory<br>Please upload some albums to ", self::__get('galleryBasePath');
			exit;
		}
	} //End Constructor

	// Getters and Setters
	public function __set($key,$val) {
		$this->$key=$val;
	}
	public function __get($key) {
		return $this->$key;
	}

	// Weblery Methods

	public function cleanAlbumCache() {
		if ((is_dir(self::__get('selectedAlbumCachePath')))) {
			$tempDirectoryArray = self::getPhotoArray();
			$albumCacheArray = self::dirsearch(self::__get('selectedAlbumCachePath'),'/^(tn_|320_|640_)/i',0,0);
			$files = array();
			foreach ($tempDirectoryArray as $currentImage) {
				$tempThumb = "tn_" . md5($currentImage);
				$temp320 = "320_" . md5($currentImage);
				$temp640 = "640_" . md5($currentImage);
				if (in_array($tempThumb, $albumCacheArray)) { $files[] = $tempThumb; }
				if (in_array($temp320, $albumCacheArray)) { $files[] = $temp320; }
				if (in_array($temp640, $albumCacheArray)) { $files[] = $temp640; }
			}
			$filesToDelete = array_diff($albumCacheArray, $files);
			
			foreach ($filesToDelete as $fileToDelete) {
				unlink(self::__get('selectedAlbumCachePath') . $fileToDelete);
			}
		}
	}
	
	public function isInitialized() {
		//Check if this album's cache directory exists
		if ( (is_dir(self::__get('selectedAlbumCachePath'))) ) {
			$tempDirectoryArray = self::getPhotoArray();
			$albumCacheArray = self::dirsearch(self::__get('selectedAlbumCachePath'),'/^(tn_|320_|640_)/i',0,0);
			if (count($albumCacheArray) == (3 * count($tempDirectoryArray))) {
				return true;
			//The count of cached images is smaller than the photo array
			} else { return false; }
		//Cache directory doesn't exist
		} else { return false; }
	} // End isInitialized method

	protected function initializeAlbum($currentStep) {
		//Check to see if this album has a folder in the album_cache to place it's images.
		//If it doesn't, then create it.
		if ( !(is_dir(self::__get('selectedAlbumCachePath'))) ) {
			mkdir(self::__get('selectedAlbumCachePath') , 0777) or
				die("Failed to create directory. Please make sure that the " . self::__get('albumCachePath') . " is readable and writable by the web server");
		} ?>
		<style>
		body {
		margin:10px;
		}
		</style>
		<h1>Album Initialization</h1>
		<?php
		$numberOfSteps = 3;
		$nextStep = $currentStep + 1;
		
		switch($currentStep) {
			case 1 :
				self::regenThumbs('320',self::__get('selectedAlbumCachePath'));
				echo '<h2>Step', $currentStep, " of ", $numberOfSteps, ' Complete.</h2>';
				$continueLinkText = "Generate 640 width images";
				break;
			case 2 :
				self::regenThumbs('640',self::__get('selectedAlbumCachePath'));
				echo '<h2>Step ', $currentStep, " of ", $numberOfSteps, ' Complete.</h2>';
				$continueLinkText = "Generate thumbnail images";
				break;
			case 3 :
				self::regenThumbs('',self::__get('selectedAlbumCachePath'));
				echo '<h2>Step ', $currentStep, " of ", $numberOfSteps, ' Complete.</h2>';
				$continueLinkText = "View your new album";
				break;
			default :
				echo '<p>This album first needs to be initialized.</p>';
				$continueLinkText = "Generate 320 width images";
				break;
		}
		?>
		
		<div id="progressbar" style="display:none;"><img id="loading-img" src="<?php echo self::__get('webleryBasePath'); ?>assets/img/loading.gif" alt="Generating Images... (loading animation)" /></div>
		<p id="redirect-text">This page will continue automatically in 5 seconds.</p>
		<p id="explanation-text">If for some reason it does not continue, click the step link below.</p>
		
		<?php
		if ($currentStep <= 2) {
			echo '<p id="continue-link-paragraph">Step ', $nextStep, ': <a href="#null" id="continue-init-link" onClick="setPageLoading(\'', self::__get('selectedAlbum'), '\',\'', $nextStep, '\');">', $continueLinkText, '</a></p>';
		} else {
			echo '<p id="continue-link-paragraph"><a href="#null" id="continue-init-link" onClick="window.location=\'', self::__get('baseStartPage'), '?selectedAlbum=', self::__get('selectedAlbum'), '\';">', $continueLinkText, '</a></p>';
		}
		?>
		<script type="text/javascript">
			function setPageLoading(thisAlbum, thisStep) {
				$('#progressbar').css('display','block');
				$("#explanation-text").html("Please do not use your browser's back or refresh buttons at this time.");
				$("#redirect-text").html('');
				$("#continue-link-paragraph").html("");
				jQuery.get("<?php echo self::__get('webleryBasePath'); ?>weblery.php", { selectedAlbum : thisAlbum, step : thisStep },  function(data) { $("#weblery-content").html(data); });
			}
		
			window.setTimeout(function() { 
				$("#continue-init-link").click();
			}, 5000);
		</script>
	<?php
	} // End initializeAlbum method

	//Returns an array of the albums in the weblery
	public function getAlbumArray() {
		if ($galleryHandle = opendir(self::__get('initGalleryBasePath'))) {
			$directoryArray = array();
			while (false !== ($file = readdir($galleryHandle))) {
		        if (!(preg_match("/^[._]/", $file))) {
					$directoryArray[] = $file;
				}
		    }
			closedir($galleryHandle);
		} else {
			die("Error opening gallery directory");
		}
		sort($directoryArray);
		
		return $directoryArray;
	} // End getAlbumArray method

	//Returns a list of links for the albums in the weblery
	public function getAlbumList() {
		$tempDirectoryArray = self::getAlbumArray();
		$dirCount = 1;
		$albumList = "";
		foreach ($tempDirectoryArray as $dirKey => $dirVal) {
			$selected = "";
			$currentSelectedAlbum = self::__get('selectedAlbum');
			if (isset($currentSelectedAlbum) && $currentSelectedAlbum == $dirVal) {
				$selected = " class=\"selected\"";
			} else if ($dirCount == 1 && !isset($currentSelectedAlbum)) {
				$selected = " class=\"selected\"";
			}
			$albumList .= "<li" . $selected . "><a href=\"?selectedAlbum=" . $dirVal . "\">" . str_replace("_"," ",$dirVal) . "</a></li>\n";
			$dirCount += 1;
		}
		return $albumList;
	} // End getAlbumList method

	//Returns an array of photos in an album
	public function getPhotoArray() {
		//Find the photos in the selected album
		$selectedAlbumPath = self::__get('selectedAlbumPath');
		if ($albumHandle = opendir($selectedAlbumPath)) {
			$albumArray = array();
			$albumArray = self::dirsearch($selectedAlbumPath,'/[.](jpg|jpeg|png)$/i',0,0);
		} else {
			die("Error opening selected album directory");
		}
		if (count($albumArray) <= 0) { die("There are no photos in the selected album"); }
		rsort($albumArray);
		return $albumArray;
	} // End getPhotoArray method

	public function getPhotoList() {
		$tempPhotoArray = self::getPhotoArray();
		$currentStart = self::__get('start');

		if (isset($currentStart) && is_numeric($currentStart)) {
			$leftThumbStart = $currentStart;
		} else {
			$leftThumbStart = 0;
		}
		$leftThumbEnd = $leftThumbStart + 7;
		$rightThumbStart = $leftThumbEnd + 1;
		$rightThumbEnd = $rightThumbStart + 7;

		$albumCount = 0;
		$rightThumbList = "";
		$leftThumbList = "";
		foreach ($tempPhotoArray as $albumKey => $albumVal) {
			if ($albumCount >= $leftThumbStart && $albumCount <= $rightThumbEnd) {
				$currentThumbPath = self::__get('selectedAlbumCachePath') . "tn_" . md5($albumVal);
				$currentImagePath = self::__get('selectedAlbumCachePath') . self::__get('mainImageSize') . "_" . md5($albumVal);
				$currentOriginalPath = self::__get('selectedAlbumPath') . $albumVal;
				$previewImagePath = self::__get('selectedAlbumCachePath') . "320_" . md5($albumVal);
			}
			if ($albumCount >= $rightThumbStart && $albumCount <= $rightThumbEnd) {
				$rightThumbList .= '<li><img src="' . $currentThumbPath . '" style="width:48px;display:inline;border: 1px solid #727375;" alt="' . $albumVal . '" onclick="javascript:resetCurrentImage(\'' . $currentImagePath . '\', \'' . $currentOriginalPath . '\');setTimeout(\'hidePreviewImage();\', 350);" onmouseover="javascript:previewImage(\'' . $previewImagePath . '\');" /></li>' . "\n";
			}
			if ($albumCount >= $leftThumbStart && $albumCount <= $leftThumbEnd) {
				$leftThumbList .= '<li><img src="' . $currentThumbPath . '" style="width:48px;display:inline;border: 1px solid #727375;" alt="' . $albumVal . '" onclick="javascript:resetCurrentImage(\'' . $currentImagePath . '\', \'' . $currentOriginalPath . '\');setTimeout(\'hidePreviewImage();\', 350);" onmouseover="javascript:previewImage(\'' . $previewImagePath . '\');" /></li>' . "\n";
			}
			$albumCount += 1;
		}
		
		return $leftThumbList . "|" . $rightThumbList;
	} // End getPhotoList method

	public function displayWeblery() {
		$currentAlbumArray = self::getPhotoArray();
		$currentStart = self::__get('start');
		$thumbList = split("\|", self::getPhotoList());
		$numberOfSets = ceil(count($currentAlbumArray)/16);

		if (isset($currentStart) && is_numeric($currentStart)) {
			$currentImageId = $currentStart;
		} else {
			$currentImageId = 0;
		}
		?>
		
		<script type="text/javascript">
			var thumbArray= new Array();
			var originalArray= new Array();
			var mainImageSize = "<?php echo self::__get('mainImageSize'); ?>";
			<?php
			$thumbCount = 0;
			foreach ($currentAlbumArray as $albumKey => $albumVal) {
				$currentThumbPath = self::__get('selectedAlbumCachePath') . "tn_" . md5($albumVal);
				$currentImagePath = self::__get('selectedAlbumCachePath') . self::__get('mainImageSize') . "_" . md5($albumVal);
				$currentOriginalPath = self::__get('selectedAlbumPath') . $albumVal;
				echo "thumbArray[", $thumbCount, "] = '", $currentImagePath, "';\n";
				echo "originalArray[", $thumbCount, "] = '", $currentOriginalPath, "';\n";
				$thumbCount += 1;
			}
			if ($currentImageId == 0 ) {
				$prevThumbPosition = $thumbCount - 1;
			} else {
				$prevThumbPosition = $currentImageId - 1;
			}
			?>
		</script>
		<?php require_once(self::__get('layoutFile')); ?>
		<script type="text/javascript">
			function changeImage(arrayPosition) {
				if (arrayPosition == 0 && <?php echo $currentImageId; ?> != 0) {
					window.location = "<?php echo $_SERVER['PHP_SELF'],'?selectedAlbum=', self::__get('selectedAlbum');?>";
					return;
				}
				if (arrayPosition < <?php echo $currentImageId; ?>) {
					window.location = "<?php echo $_SERVER['PHP_SELF'],'?selectedAlbum=', self::__get('selectedAlbum'), '&start=';?>"+(<?php echo $currentImageId; ?>-16);
					return;
				} else if (arrayPosition >= <?php echo $currentImageId+16; ?>) {
					if (arrayPosition == <?php echo count($currentAlbumArray)-1; ?>) {
						lastStartNumber = <?php echo $lastStartNumber; ?>;
					} else {
						lastStartNumber = arrayPosition;
					}
					if ($("#next-image-link").attr("play") == "true") { playingSlideshow = "&play=1"; } else { playingSlideshow = ''; }
					window.location = "<?php echo $_SERVER['PHP_SELF'],'?selectedAlbum=', self::__get('selectedAlbum'), '&start=';?>"+(lastStartNumber)+playingSlideshow;
					return;
				} else {
					resetCurrentImage(thumbArray[arrayPosition], originalArray[arrayPosition]);
					prevArrayPosition = arrayPosition - 1;
					if (prevArrayPosition < 0) { prevArrayPosition = thumbArray.length-1; }
					nextArrayPosition = arrayPosition + 1;
					if (nextArrayPosition >= thumbArray.length) { nextArrayPosition = 0; }
					document.getElementById('previous-image-cell').innerHTML = '<a href="#null" id="prev-image-link" onclick="javascript:changeImage(' + prevArrayPosition + ');return false;"><img src="<?php echo self::__get('imgBasePath'); ?>skipBackward.png" alt="Previous Image" /></a>';
					document.getElementById('next-image-cell').innerHTML = '<a href="#null" id="next-image-link" onclick="javascript:changeImage(' + nextArrayPosition + ');return false;"><img src="<?php echo self::__get('imgBasePath'); ?>skipForward.png" alt="Next Image" /></a>';
				}
			}
			
			function resetCurrentImage(imagePath, originalImagePath) {
				document.getElementById('current-image-img').src = imagePath;
				document.getElementById('current-image-original-img').src = originalImagePath;
				document.getElementById('view-original').href = originalImagePath;
				for(var i = 0; i < thumbArray.length; i++) {
					if(thumbArray[i] === imagePath) {
						prevArrayPosition = i - 1;
						if (prevArrayPosition < 0) { prevArrayPosition = thumbArray.length-1; }
						nextArrayPosition = i + 1;
						if (nextArrayPosition >= thumbArray.length) { nextArrayPosition = 0; }
					}
				}
				document.getElementById('previous-image-cell').innerHTML = '<a href="#null" id="prev-image-link" onclick="javascript:changeImage(' + prevArrayPosition + ');return false;"><img src="<?php echo self::__get('imgBasePath'); ?>skipBackward.png" alt="Previous Image" /></a>';
				document.getElementById('next-image-cell').innerHTML = '<a href="#null" id="next-image-link" onclick="javascript:changeImage(' + nextArrayPosition + ');return false;"><img src="<?php echo self::__get('imgBasePath'); ?>skipForward.png" alt="Next Image" /></a>';
			}
			
			function previewImage(imagePath) {
				document.getElementById('preview-image-img').src = imagePath;
				setTimeout( "showPreviewImage();", 250); 
			}
			
			function showPreviewImage() {
				document.getElementById('current-image-img').style.opacity = "0.4";
				document.getElementById('current-image-img').style.filter = "alpha(opacity=40)";
				document.getElementById('preview-image').style.display = "block";
			}
			
			function hidePreviewImage() {
				document.getElementById('current-image-img').style.opacity = "1";
				document.getElementById('current-image-img').style.filter = "alpha(opacity=100)";
				document.getElementById('preview-image').style.display = "none";
			}
		</script>
<?php
	} // End displayWeblery method

	//Thumbnail Methods

	public function regenThumbs($regenType, $currentAlbumCachePath) {
		$currentAlbum = self::__get('selectedAlbum');
		if (baseName($_SERVER['SCRIPT_NAME']) == 'weblery.php') {
			$currentBasePath = self::__get('initGalleryBasePath');
		} else {
			$currentBasePath = self::__get('galleryBasePath');
		}
		
		if (isset($currentAlbum) && strlen($currentAlbum)) {
			$imagefolder=$currentBasePath.$currentAlbum."/";
			$thumbsfolder=$currentAlbumCachePath;
			$pics=self::dirsearch($imagefolder,'/[.](jpg|jpeg|png)$/i',0,0);
			if ($pics[0]!="") {
				foreach ($pics as $p) {
					$currentImagePath = $imagefolder.$p;
					set_time_limit(20);
					switch ($regenType) {
						case "320" :
							$s320DestPath = $thumbsfolder."320_".md5($p);
							if (!is_file($s320DestPath)) {
								self::resizeImage($currentImagePath,$s320DestPath,320);
							}
							break;
						case "640" :
							$s640DestPath = $thumbsfolder."640_".md5($p);
							if (!is_file($s640DestPath)) {
								self::resizeImage($currentImagePath,$s640DestPath,640);
							}
							break;
						default :
							$thumbDestPath = $thumbsfolder."tn_".md5($p);
							if (!is_file($thumbDestPath)) {
								self::createthumb($currentImagePath,$thumbDestPath,self::__get('defaultThumbWidth'),self::__get('defaultThumbHeight'),1);
							}
							$otherType = 'thumbnails';
							break;
					}
				}
			}
			if (isset($otherType) && $otherType = 'thumbnails') { $regenType = $otherType; }
		}
	} // End Regen Thumbs Method

	/*Filters out thumbnails*/
	protected function ditchtn($arr,$thumbname) {
		$tmparr = '';
		foreach ($arr as $item)	{
			if (!preg_match("/^".$thumbname."/",$item)){$tmparr[]=$item;}
		}
		return $tmparr;
	}

	/* Creates a resized image */
	protected function createthumb($name,$filename,$new_w,$new_h,$forceSquare) {
		$system=explode(".",$name);
		if (preg_match("/jpg|jpeg/i",$system[1])){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/png/i",$system[1])){$src_img=imagecreatefrompng($name);}
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);
		if ($forceSquare) {
			 if($old_x > $old_y) {
			 	$biggestSide = $old_x;
			} else {
				$biggestSide = $old_y;
			}
			//The crop size will be half that of the largest side 
			$cropPercent = .5; // This will zoom in to 50% zoom (crop)
			$cropWidth   = $biggestSide*$cropPercent; 
			$cropHeight  = $biggestSide*$cropPercent; 
			
			//getting the top left coordinate
			$cropX = ($old_x-$cropWidth)/2;
			$cropY = ($old_y-$cropHeight)/2;
			
			$old_x=$cropWidth;
			$old_y=$cropHeight;
			$thumb_w=$new_w;
			$thumb_h=$new_h;
		} else {
			if ($old_x == $old_y) {
				$thumb_w=$new_w;
				$thumb_h=$new_h;
			} else if ($old_x < $old_y) {
				$thumb_w=$old_x*($new_w/$old_y);
				$thumb_h=$new_h;
			} else if ($old_x > $old_y) {
				$thumb_w=$new_w;
				$thumb_h=$old_y*($new_h/$old_x);
			}
			$cropX=0;
			$cropY=0;
		}
		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,$cropX,$cropY,$thumb_w,$thumb_h,$old_x,$old_y); 
		if (preg_match("/png/",$system[1])) {
			imagepng($dst_img,$filename); 
		} else {
			imagejpeg($dst_img,$filename); 
		}
		imagedestroy($dst_img); 
		imagedestroy($src_img); 
	}

	protected function resizeImage($name,$filename,$new_size) {
		$system=explode(".",$name);
		if (preg_match("/jpg|jpeg/i",$system[1])){
			$src_img=imagecreatefromjpeg($name) or die("Issue creating image for " . $name);
		}
		if (preg_match("/png/i",$system[1])){$src_img=imagecreatefrompng($name);}
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);
		$cropX=0;
		$cropY=0;

		if ($old_x == $old_y) {
			$thumb_w=$new_size;
			$thumb_h=$new_size;
		} else if ($old_x < $old_y) {
			$thumb_w=$old_x*($new_size/$old_y);
			$thumb_h=$new_size;
		} else if ($old_x > $old_y) {
			$thumb_w=$new_size;
			$thumb_h=$old_y*($new_size/$old_x);
		}
		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,$cropX,$cropY,$thumb_w,$thumb_h,$old_x,$old_y); 
		if (preg_match("/png/",$system[1])) {
			imagepng($dst_img,$filename); 
		} else {
			imagejpeg($dst_img,$filename); 
		}
		imagedestroy($dst_img); 
		imagedestroy($src_img); 
	}

	/*
	 * Function:     dirsearch()
	 * Return Value: array of strings (filenames matching pattern)
	 * Description:  Recursively searches a directory for files matching the regex specified by $filter.
	 * Arguments:
	 *   $dir        path to directory that will be searched
	 *   $filter     search regex - defaults to match everything
	 *   $maxdepth   maximum recursion depth - defaults to 100
	 *   $mindepth   minimum recursion depth - defaults to 0
	 *
	 *   $level      current level                 - used by recursive calls
	 *   $basedir    path prefix to matching files - used by recursive calls
	 */
	protected function dirsearch($dir,$filter='/.*/',$maxdepth=100,$mindepth=0,$level=0,$basedir='')
	{
		// prevent excessive recursion
		if ( $level >= 100 || $level > $maxdepth ) {
			return array();
		}

		// open the specified directory, return an empty array if opendir() fails
		if ( ($handle = opendir($dir)) === false ) {
			return array();
		}

		// we'll store the matching filenames here
		$files = array();

		// cycle through the files in the directory
		while (($file = readdir($handle)) !== false ) {

			// skip files that start with a .
			if ( preg_match('/^[.\_]/', $file) ) { continue; }

			// if the file is a directory, recurse into it
			if ( is_dir($dir.'/'.$file) ) {
				foreach ( self::dirsearch($dir.'/'.$file, $filter, $maxdepth, $mindepth, $level+1,"$file/") as $f) {
					array_push($files,$f);
				}
			}

			// add files that match the filter regex
			if ( is_file($dir.'/'.$file) && preg_match($filter, $file) ) {
				// only add the files if we've recursed greater than or equal to mindepth
				if ( $level >= $mindepth ) { array_push($files,$basedir.$file); }
			}
		}

		closedir($handle);
		return $files;
	}

} //End weblery class ?>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/customWeblery.js"></script>
<?php if (!($weblery->__get('stillInitializing'))) {
	$weblery->displayWeblery(); ?>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jquery.exif.js"></script>
<script type="text/javascript">
$.preloadImages(mainImageSize,thumbArray,originalArray);
</script>
<?php } ?>
</div>
