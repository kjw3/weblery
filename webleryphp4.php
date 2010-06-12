<?php
//Author: Kevin Jones
//Email: kevin.d.jones@gmail.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 06/17/2009
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
include 'configuration.php'; //Get Weblery's Configuration

class weblery {
	var $webleryBasePath = '';
	var $galleryBasePath = '';
	var $initGalleryBasePath = '';
	var $imgBasePath = '';
	var $stillInitializing = false;
	var $baseStartPage = '';
	var $albumCachePath = '';
	var $selectedAlbumCachePath = '';
	var $start = 0;
	var $selectedAlbum = '';
	var $enablePreview = true;

	function weblery() {
		$this->__set('webleryBasePath',confWebleryBasePath); //Directory where weblery.php is located
		$this->__set('galleryBasePath',$this->__get('webleryBasePath') . confGalleryBasePath); //Directory where weblery albums are located

		if (baseName($_SERVER['SCRIPT_NAME']) == 'webleryphp4.php') {
			$this->__set('initGalleryBasePath',confGalleryBasePath); //Directory where weblery.php is located
			$this->__set('albumCachePath','assets/album_cache/'); //Directory where weblery album images are created and stored
		} else {
			$this->__set('initGalleryBasePath',$this->__get('galleryBasePath')); //Directory where weblery.php is located
			$this->__set('albumCachePath',$this->__get('webleryBasePath') . 'assets/album_cache/'); //Directory where weblery album images are created and stored
		}

		$this->__set('imgBasePath',$this->__get('webleryBasePath') . 'assets/img/'); //Directory where weblery images is located
		$this->__set('layoutFile',$this->__get('webleryBasePath') . 'assets/layout/'.confLayoutFile);
		$this->__set('baseStartPage',confBaseStartPage);
		$this->__set('defaultThumbWidth',confDefaultThumbWidth);
		$this->__set('defaultThumbHeight',confDefaultThumbHeight);
		$this->__set('mainImageSize',confMainImageSize);
		$this->__set('enablePreview',confEnablePreview);

		$tempAlbumArray = $this->getAlbumArray();
		if (count($tempAlbumArray)) {
			if (isset($_GET['selectedAlbum']) && in_array($_GET['selectedAlbum'],$tempAlbumArray) && strlen($_GET['selectedAlbum']) > 0) {
				$this->__set('selectedAlbum',$_GET['selectedAlbum']);
			} else {
				$this->__set('selectedAlbum',$tempAlbumArray[0]);
			}
			if (isset($_GET['start']) && strlen($_GET['start']) > 0) {
				$this->__set('start',$_GET['start']);
			} else {
				$this->__set('start',0);
			}
			$this->__set('selectedAlbumPath',$this->__get('initGalleryBasePath').$this->__get('selectedAlbum')."/");
			$this->__set('selectedAlbumCachePath',$this->__get('albumCachePath').$this->__get('selectedAlbum')."/");

			$this->cleanAlbumCache();

			if ($this->isInitialized()) {
				$this->__set('stillInitializing',false);
			} else {
				$this->__set('stillInitializing',true);
				if (isset($_GET['step']) && strlen($_GET['step']) > 0 && is_numeric($_GET['step'])) {
					$this->__set('initStep',$_GET['step']);
				} else {
					$this->__set('initStep',0);
				}
				$this->initializeAlbum($this->__get('initStep'));
			}
		} else {
			echo "No Albums detected in the Album directory<br>Please upload some albums to ", $this->__get('galleryBasePath');
			exit;
		}
	} //End Constructor

	// Getters and Setters
	function __set($key,$val) {
		$this->$key=$val;
	}
	function __get($key) {
		return $this->$key;
	}

	// Weblery Methods

	function cleanAlbumCache() {
		if (is_dir($this->__get('selectedAlbumCachePath'))) {
			$tempDirectoryArray = $this->getPhotoArray();
			$albumCacheArray = $this->dirsearch($this->__get('selectedAlbumCachePath'),'/^(tn_|320_|640_)/i',0,0);
			$files = array();
			foreach ($tempDirectoryArray as $currentImage) {
				if (in_array("tn_" . md5($currentImage), $albumCacheArray)) { $files[] = "tn_" . md5($currentImage); }
				if (in_array("320_" . md5($currentImage), $albumCacheArray)) { $files[] = "320_" . md5($currentImage); }
				if (in_array("640_" . md5($currentImage), $albumCacheArray)) { $files[] = "640_" . md5($currentImage); }
			}
			$filesToDelete = array_diff($albumCacheArray, $files);

			foreach ($filesToDelete as $fileToDelete) {
				unlink($this->__get('selectedAlbumCachePath') . $fileToDelete);
			}
		}
	}

	function isInitialized() {
		if (is_dir($this->__get('selectedAlbumCachePath'))) {
			$tempDirectoryArray = $this->getPhotoArray();
			$albumCacheArray = $this->dirsearch($this->__get('selectedAlbumCachePath'),'/^(tn_|320_|640_)/i',0,0);
			if (count($albumCacheArray) == (3 * count($tempDirectoryArray))) {
				return true;
			} else { return false; } //Not enough images in the cache folder
		} else { return false; } //No cache folder exists for this album
	} // End isInitialized method

	function initializeAlbum($currentStep) {
		$oldUmask = umask(0);
		if ( !(is_dir($this->__get('selectedAlbumCachePath'))) ) {
			mkdir($this->__get('selectedAlbumCachePath') , 0777) or
				die("Failed to create directory. Please make sure that the " . $this->__get('albumCachePath') . " is readable and writable by the web server");
		}
		echo '<style>#init-container{margin:10px;}</style>';
		echo '<div id="init-container">';
		echo '<h1>Album Initialization</h1>';

		$numberOfSteps = 3;
		$nextStep = $currentStep + 1;

		switch($currentStep) {
			case 1 :
				$this->regenThumbs('320',$this->__get('selectedAlbumCachePath'));
				echo '<h2 id="h2-status" style="font-size:small;">Step', $currentStep, " of ", $numberOfSteps, ' Complete</h2>';
				$continueLinkText = "Generate 640 width images";
				break;
			case 2 :
				$this->regenThumbs('640',$this->__get('selectedAlbumCachePath'));
				echo '<h2 id="h2-status" style="font-size:small;">Step ', $currentStep, " of ", $numberOfSteps, ' Complete</h2>';
				$continueLinkText = "Generate thumbnail images";
				break;
			case 3 :
				$this->regenThumbs('',$this->__get('selectedAlbumCachePath'));
				echo '<h2 id="h2-status" style="font-size:small;">Step ', $currentStep, " of ", $numberOfSteps, ' Complete</h2>';
				$continueLinkText = "View your new album";
				break;
			default :
				echo '<h2 id="h2-status" style="font-size:small;">This album first needs to be initialized</h2>';
				$continueLinkText = "Generate 320 width images";
				break;
		} ?>
			
		<div id="progressbar" style="display:none;"><img id="loading-img" src="<?php echo $this->__get('webleryBasePath'); ?>assets/img/loading.gif" alt="Generating Images... (loading animation)" /></div>
		<p id="redirect-text">This page will continue automatically.</p>
		<p id="explanation-text">If for some reason it does not, click the link below.</p>
			
		<?php
		if ($currentStep <= 2) {
			echo '<p id="continue-link-paragraph">Step ', $nextStep, ': <a href="#null" id="continue-init-link" onClick="setPageLoading(\'', $this->__get('selectedAlbum'), '\',\'', $nextStep, '\');">', $continueLinkText, '</a></p>';
		} else {
			echo '<p id="continue-link-paragraph"><a href="#null" id="continue-init-link" onClick="window.location=\'', $this->__get('baseStartPage'), '?selectedAlbum=', $this->__get('selectedAlbum'), '\';">', $continueLinkText, '</a></p>';
		}
		?>
		</div>
		<script type="text/javascript">
			function setPageLoading(thisAlbum, thisStep) {
				$('#progressbar').css('display','block');
				$("#explanation-text").html("Please do not use your browser's back or refresh buttons at this time.");
				$("#h2-status,#redirect-text,#continue-link-paragraph").html('');
				jQuery.get("<?php echo $this->__get('webleryBasePath'); ?>webleryphp4.php", { selectedAlbum : thisAlbum, step : thisStep },  function(data) { $("#weblery-content").html(data); $("#continue-init-link").click(); });
			}
			
			$(document).ready(function(){ $("#continue-init-link").click(); });
		</script>
	<?php

		umask($oldUmask);

	} // End initializeAlbum method

	//Returns an array of the albums in the weblery
	function getAlbumArray() {
		$directoryArray = array();

		if ($galleryHandle = opendir($this->__get('initGalleryBasePath'))) {
			while (false !== ($file = readdir($galleryHandle))) {
		        if (!(preg_match("/^[._]/", $file))) {
					$directoryArray[] = $file;
				}
		    }
			closedir($galleryHandle);
		} else { die("Error opening gallery directory"); }

		sort($directoryArray);

		return $directoryArray;
	} // End getAlbumArray method

	//Returns a list of links for the albums in the weblery
	function getAlbumList() {
		$tempDirectoryArray = $this->getAlbumArray();
		$dirCount = 1;
		$albumList = "";

		foreach ($tempDirectoryArray as $dirVal) {
			$selected = "";
			$currentSelectedAlbum = $this->__get('selectedAlbum');
			if ((isset($currentSelectedAlbum) && $currentSelectedAlbum == $dirVal) || ($dirCount == 1 && !isset($currentSelectedAlbum))) {
				$selected = " class=\"selected\"";
			}
			$albumList .= "<li" . $selected . "><a href=\"?selectedAlbum=" . $dirVal . "\">" . str_replace("_"," ",$dirVal) . "</a></li>\n";
			$dirCount += 1;
		}

		return $albumList;
	} // End getAlbumList method

	//Returns an array of photos in an album
	function getPhotoArray() {
		$selectedAlbumPath = $this->__get('selectedAlbumPath');
		$albumArray = array();

		if ($albumHandle = opendir($selectedAlbumPath)) {
			$albumArray = $this->dirsearch($selectedAlbumPath,'/[.](jpg|jpeg|png)$/i',0,0);
		} else { die("Error opening selected album directory"); }

		if (count($albumArray) <= 0) { die("There are no photos in the selected album"); }

		rsort($albumArray);

		return $albumArray;
	} // End getPhotoArray method

	function getPhotoList() {
		$tempPhotoArray = $this->getPhotoArray();
		$currentStart = $this->__get('start');

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
				$currentThumbPath = $this->__get('selectedAlbumCachePath') . "tn_" . md5($albumVal);
				$currentImagePath = $this->__get('selectedAlbumCachePath') . $this->__get('mainImageSize') . "_" . md5($albumVal);
				$currentOriginalPath = $this->__get('selectedAlbumPath') . $albumVal;
				$previewImagePath = $this->__get('selectedAlbumCachePath') . "320_" . md5($albumVal);
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

	function displayWeblery() {
		$currentAlbumArray = $this->getPhotoArray();
		$currentStart = $this->__get('start');
		$thumbList = split("\|", $this->getPhotoList());
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
			var mainImageSize = "<?php echo $this->__get('mainImageSize'); ?>";
			<?php
			$thumbCount = 0;
			foreach ($currentAlbumArray as $albumKey => $albumVal) {
				$currentThumbPath = $this->__get('selectedAlbumCachePath') . "tn_" . md5($albumVal);
				$currentImagePath = $this->__get('selectedAlbumCachePath') . $this->__get('mainImageSize') . "_" . md5($albumVal);
				$currentOriginalPath = $this->__get('selectedAlbumPath') . $albumVal;
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
		<?php
		$albumList = $this->getAlbumList();
		$selectedAlbum = $this->__get('selectedAlbum');
		$selectedAlbumPath = $this->__get('selectedAlbumPath');
		$imageBasePath = $this->__get('imgBasePath');
		$selectedAlbumCachePath = $this->__get('selectedAlbumCachePath');
		$mainImageSize = $this->__get('mainImageSize');
		
		require_once($this->__get('layoutFile')); ?>
		<script type="text/javascript">
			function changeImage(arrayPosition) {
				if (arrayPosition == 0 && <?php echo $currentImageId; ?> != 0) {
					window.location = "<?php echo $_SERVER['PHP_SELF'],'?selectedAlbum=', $this->__get('selectedAlbum');?>";
					return;
				}
				if (arrayPosition < <?php echo $currentImageId; ?>) {
					window.location = "<?php echo $_SERVER['PHP_SELF'],'?selectedAlbum=', $this->__get('selectedAlbum'), '&start=';?>"+(<?php echo $currentImageId; ?>-16);
					return;
				} else if (arrayPosition >= <?php echo $currentImageId+16; ?>) {
					if (arrayPosition == <?php echo count($currentAlbumArray)-1; ?>) {
						lastStartNumber = <?php if (!isset($lastStartNumber)) { echo 0; } else { echo $lastStartNumber; } ?>;
					} else {
						lastStartNumber = arrayPosition;
					}
					if ($("#next-image-link").attr("play") == "true") { playingSlideshow = "&play=1"; } else { playingSlideshow = ''; }
					window.location = "<?php echo $_SERVER['PHP_SELF'],'?selectedAlbum=', $this->__get('selectedAlbum'), '&start=';?>"+(lastStartNumber)+playingSlideshow;
					return;
				} else {
					resetCurrentImage(thumbArray[arrayPosition], originalArray[arrayPosition]);
					prevArrayPosition = arrayPosition - 1;
					if (prevArrayPosition < 0) { prevArrayPosition = thumbArray.length-1; }
					nextArrayPosition = arrayPosition + 1;
					if (nextArrayPosition >= thumbArray.length) { nextArrayPosition = 0; }
					document.getElementById('previous-image-cell').innerHTML = '<a href="#null" id="prev-image-link" onclick="javascript:changeImage(' + prevArrayPosition + ');return false;"><img src="<?php echo $this->__get('imgBasePath'); ?>skipBackward.png" alt="Previous Image" /></a>';
					document.getElementById('next-image-cell').innerHTML = '<a href="#null" id="next-image-link" onclick="javascript:changeImage(' + nextArrayPosition + ');return false;"><img src="<?php echo $this->__get('imgBasePath'); ?>skipForward.png" alt="Next Image" /></a>';
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
				document.getElementById('previous-image-cell').innerHTML = '<a href="#null" id="prev-image-link" onclick="javascript:changeImage(' + prevArrayPosition + ');return false;"><img src="<?php echo $this->__get('imgBasePath'); ?>skipBackward.png" alt="Previous Image" /></a>';
				document.getElementById('next-image-cell').innerHTML = '<a href="#null" id="next-image-link" onclick="javascript:changeImage(' + nextArrayPosition + ');return false;"><img src="<?php echo $this->__get('imgBasePath'); ?>skipForward.png" alt="Next Image" /></a>';
			}
			
			function previewImage(imagePath) {
				document.getElementById('preview-image-img').src = imagePath;
				setTimeout( "showPreviewImage();", 250); 
			}
			
			function showPreviewImage() {
				document.getElementById('current-image-img').style.opacity = "0.4";
				document.getElementById('current-image-img').style.filter = "alpha(opacity=40)";
				$("#preview-image").show("slow");
			}
			
			function hidePreviewImage() {
				document.getElementById('current-image-img').style.opacity = "1";
				document.getElementById('current-image-img').style.filter = "alpha(opacity=100)";
				$("#preview-image").hide("slow");
			}
		</script>
<?php
	} // End displayWeblery method

	//Thumbnail Methods

	function regenThumbs($regenType, $currentAlbumCachePath) {
		$currentAlbum = $this->__get('selectedAlbum');

		if (baseName($_SERVER['SCRIPT_NAME']) == 'webleryphp4.php') {
			$currentBasePath = $this->__get('initGalleryBasePath');
		} else {
			$currentBasePath = $this->__get('galleryBasePath');
		}

		if (isset($currentAlbum) && strlen($currentAlbum)) {
			$imagefolder=$currentBasePath.$currentAlbum."/";
			$thumbsfolder=$currentAlbumCachePath;
			$pics=$this->dirsearch($imagefolder,'/[.](jpg|jpeg|png)$/i',0,0);

			if ($pics[0]!="") {
				foreach ($pics as $p) {
					$currentImagePath = $imagefolder.$p;
					set_time_limit(20);
					switch ($regenType) {
						case "320" :
							$s320DestPath = $thumbsfolder."320_".md5($p);
							if (!is_file($s320DestPath)) {
								$this->resizeImage($currentImagePath,$s320DestPath,320);
							}
							break;
						case "640" :
							$s640DestPath = $thumbsfolder."640_".md5($p);
							if (!is_file($s640DestPath)) {
								$this->resizeImage($currentImagePath,$s640DestPath,640);
							}
							break;
						default :
							$thumbDestPath = $thumbsfolder."tn_".md5($p);
							if (!is_file($thumbDestPath)) {
								$this->createthumb($currentImagePath,$thumbDestPath,$this->__get('defaultThumbWidth'),$this->__get('defaultThumbHeight'),1);
							}
							$otherType = 'thumbnails';
							break;
					}
				}
			}

			if (isset($otherType) && $otherType = 'thumbnails') { $regenType = $otherType; }
		}
	} // End Regen Thumbs Method

	/* Creates a resized image */
	function createthumb($name,$filename,$new_w,$new_h,$forceSquare) {
		if (preg_match("/(.jpg|.jpeg)$/i",$name)){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/(.png)$/i",$name)){$src_img=imagecreatefrompng($name);}
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
		if (preg_match("/(.png)$/",$name)) {
			imagepng($dst_img,$filename); 
		} else {
			imagejpeg($dst_img,$filename); 
		}
		imagedestroy($dst_img); 
		imagedestroy($src_img); 
	} // End Create Thumb Method

	function resizeImage($name,$filename,$new_size) {
		if (preg_match("/(.jpg|.jpeg)$/i",$name)){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/(.png)$/i",$name)){$src_img=imagecreatefrompng($name);}
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);
		$cropX=0;
		$cropY=0;
		$thumb_w = 0;
		$thumb_h = 0;

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
		if (preg_match("/(.png)$/",$name)) {
			imagepng($dst_img,$filename); 
		} else {
			imagejpeg($dst_img,$filename); 
		}
		imagedestroy($dst_img); 
		imagedestroy($src_img); 
	} // End Resize Image Method

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
	function dirsearch($dir,$filter='/.*/',$maxdepth=100,$mindepth=0,$level=0,$basedir='') {
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
				foreach ( $this->dirsearch($dir.'/'.$file, $filter, $maxdepth, $mindepth, $level+1,"$file/") as $f) {
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
	} // End dirSearch Method

} //End weblery class ?>

<link type="text/css"  rel="stylesheet" href="<?php echo confWebleryBasePath; ?>assets/js/jqueryUI/css/smoothness/jquery-ui-1.7.1.custom.css" />
<link type="text/css" rel="stylesheet"  href="<?php echo confWebleryBasePath; ?>assets/css/weblery.css" />
<script type="text/javascript" src="<?php echo confWebleryBasePath; ?>assets/js/jquery-1.3.2.min.js"></script>

<?php
//Instantiate the gallery
$weblery = new weblery();

if (!($weblery->__get('stillInitializing'))) {
	$weblery->displayWeblery();
}
?>

<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jqueryUI/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/customWeblery.js"></script>

<?php if (!($weblery->__get('stillInitializing'))) { ?>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jquery.exif.js"></script>

<?php if (confEnablePreloadImages) { ?>
	<script type="text/javascript">$(document).ready(function(){ $.preloadImages(mainImageSize,thumbArray,originalArray,<?php echo confEnablePreloadImages; ?>,<?php if (isset($_GET['start']) && is_numeric($_GET['start'])) { echo $_GET['start']; } else { echo 0; } ?>);});</script>
<?php }
} ?>
</div>