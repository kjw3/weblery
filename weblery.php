<?php
//Author: Kevin Jones
//Email: kevin.d.jones@gmail.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 11/16/2009
//Copyright: Kevin Jones 2009
//License: ./License.txt
//Manual: ./manual.pdf
//
//Note: You should find no need to edit this file
//
//Configuration Parameters are set in configuration.php
//Please read over the manual.pdf file to learn about configuration settings

function getMemoryInMb() {
	return round((memory_get_usage()/1024)/1024, 2);
}
?>

<div id="weblery-content">

<?php
echo "before configuration:", getMemoryInMb(), "mb<br />";

include 'configuration.php'; //Get Weblery's Configuration

echo "before class:", getMemoryInMb(), "mb<br />";

class weblery {
	protected static $webleryBasePath = '';
	protected static $galleryBasePath = '';
	protected static $initGalleryBasePath = '';
	protected static $imgBasePath = '';
	protected static $stillInitializing = false;
	protected static $baseStartPage = '';
	protected static $albumCachePath = '';
	protected static $selectedAlbumCachePath = '';
	protected static $enablePreview = true;
	public $start = 0;
	public $selectedAlbum = '';

	function __construct() {
echo "start constructor:", getMemoryInMb(), "mb<br />";
		self::__set('webleryBasePath',confWebleryBasePath); //Directory where weblery.php is located
		self::__set('galleryBasePath',self::__get('webleryBasePath') . confGalleryBasePath); //Directory where weblery albums are located
		if (baseName($_SERVER['SCRIPT_NAME']) == 'weblery.php') {
			self::__set('initGalleryBasePath',confGalleryBasePath);
			self::__set('albumCachePath','assets/album_cache'); //Directory where weblery album images are created and stored
		} else {
			self::__set('initGalleryBasePath',self::__get('galleryBasePath'));
			self::__set('albumCachePath',self::__get('webleryBasePath') . 'assets/album_cache'); //Directory where weblery album images are created and stored
		}
		self::__set('imgBasePath',self::__get('webleryBasePath') . 'assets/img/');
		self::__set('layoutFile',self::__get('webleryBasePath') . 'assets/layout/'.confLayoutFile);
		self::__set('baseStartPage',confBaseStartPage);
		self::__set('defaultThumbWidth',confDefaultThumbWidth);
		self::__set('defaultThumbHeight',confDefaultThumbHeight);
		self::__set('mainImageSize',confMainImageSize);
		self::__set('enablePreview',confEnablePreview);

echo "constructor after section 1:", getMemoryInMb(), "mb<br />";

		$tempAlbumArray = self::getAlbumArray();

echo "constructor after section 2:", getMemoryInMb(), "mb<br />";

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
			self::__set('selectedAlbumPath',self::__get('initGalleryBasePath').'/'.self::__get('selectedAlbum'));
			self::__set('selectedAlbumCachePath',self::__get('albumCachePath').'/'.self::__get('selectedAlbum'));

			if (self::isInitialized()) {
				self::__set('stillInitializing',false);

				self::cleanAlbumCache($tempAlbumArray);
			} else {
				self::__set('stillInitializing',true);
				if (isset($_GET['step']) && strlen($_GET['step']) > 0 && is_numeric($_GET['step'])) {
					self::__set('initStep',$_GET['step']);
				} else {
					self::__set('initStep',0);
				}
echo "right before init album call", getMemoryInMb(), "mb<br />";
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

	public function cleanAlbumCache($albumDirArray) {
		//Go through and remove any cached images that are no longer needed
		if (is_dir(self::__get('selectedAlbumCachePath'))) {
			$tempDirectoryArray = self::getPhotoArray();
			$albumCacheArray = self::dirsearch(self::__get('selectedAlbumCachePath'),'/^(tn_|'.(self::__get('mainImageSize')/2).'_|'.self::__get('mainImageSize').'_)/i',0,0);
			$files = array();
			foreach ($tempDirectoryArray as $currentImage) {
				if (in_array("tn_" . md5($currentImage), $albumCacheArray)) { $files[] = "tn_" . md5($currentImage); }
				if (in_array((self::__get('mainImageSize')/2)."_" . md5($currentImage), $albumCacheArray)) { $files[] = (self::__get('mainImageSize')/2)."_" . md5($currentImage); }
				if (in_array(self::__get('mainImageSize')."_" . md5($currentImage), $albumCacheArray)) { $files[] = self::__get('mainImageSize')."_" . md5($currentImage); }
			}
			$filesToDelete = array_diff($albumCacheArray, $files);

			foreach ($filesToDelete as $fileToDelete) {
				unlink(self::__get('selectedAlbumCachePath') . '/' . $fileToDelete);
			}
		}

		//Go through and remove any album cache directory for albums that no longer exist
		$albumCacheDirectoryArray = array();
		$albumCacheDirs = array();

		if ($galleryHandle = opendir(self::__get('albumCachePath'))) {
			while (false !== ($file = readdir($galleryHandle))) {
		        if (!(preg_match("/^[._]/", $file))) {
					$albumCacheDirectoryArray[] = $file;
				}
		    }
			closedir($galleryHandle);
		} else { die("Error opening album cache directory"); }

		foreach ($albumCacheDirectoryArray as $currentAlbumCache) {

			if (is_dir(self::__get('initGalleryBasePath').'/'.$currentAlbumCache)) $albumCacheDirs[] = $currentAlbumCache;

		}

		$albumCacheToDelete = array_diff($albumCacheDirectoryArray, $albumCacheDirs);

		foreach ($albumCacheToDelete as $dirToDelete) {
			self::recursiveRemoveDirectory(self::__get('albumCachePath').$dirToDelete);
		}

	}

	public function isInitialized() {
		if (is_dir(self::__get('selectedAlbumCachePath'))) {
			$tempDirectoryArray = self::getPhotoArray();
			$albumCacheArray = self::dirsearch(self::__get('selectedAlbumCachePath'),'/^(tn_|'.(self::__get('mainImageSize')/2).'_|'.self::__get('mainImageSize').'_)/i',0,0);
			if (count($albumCacheArray) == (3 * count($tempDirectoryArray))) {
				return true;
			} else { return false; } //Not enough images in the cache folder
		} else { return false; } //No cache folder exists for this album
	} // End isInitialized method

	protected function initializeAlbum($currentStep) {
echo getMemoryInMb() . "<br />";
		$oldUmask = umask(0);
echo getMemoryInMb() . "<br />";
		if ( !(is_dir(self::__get('selectedAlbumCachePath'))) ) {
			mkdir(self::__get('selectedAlbumCachePath') , 0777) or
				die("Failed to create directory. Please make sure that the " . self::__get('albumCachePath') . " is readable and writable by the web server");
		}
echo getMemoryInMb() . "<br />";
		echo '<style>#init-container{margin:10px;}</style>';
		echo '<div id="init-container">';
		echo '<h1>Album Initialization</h1>';

echo getMemoryInMb() . "<br />";

		$numberOfSteps = 3;
		$nextStep = $currentStep + 1;

		switch($currentStep) {
			case 1 :
				self::regenThumbs((self::__get('mainImageSize')/2),self::__get('selectedAlbumCachePath'));
				echo '<h2 id="h2-status" style="font-size:small;">Step', $currentStep, " of ", $numberOfSteps, ' Complete</h2>';
				$continueLinkText = "Generate ".self::__get('mainImageSize')." width images";
				break;
			case 2 :
				self::regenThumbs(self::__get('mainImageSize'),self::__get('selectedAlbumCachePath'));
				echo '<h2 id="h2-status" style="font-size:small;">Step ', $currentStep, " of ", $numberOfSteps, ' Complete</h2>';
				$continueLinkText = "Generate thumbnail images";
				break;
			case 3 :
				self::regenThumbs('',self::__get('selectedAlbumCachePath'));
				echo '<h2 id="h2-status" style="font-size:small;">Step ', $currentStep, " of ", $numberOfSteps, ' Complete</h2>';
				$continueLinkText = "View your new album";
				break;
			default :
				echo '<h2 id="h2-status" style="font-size:small;">This album first needs to be initialized</h2>';
				$continueLinkText = "Generate ".(self::__get('mainImageSize')/2)." width images";
				break;
		}

echo getMemoryInMb() . "<br />";
?>
			
		<div id="progressbar" style="display:none;"><img id="loading-img" src="<?php echo self::__get('webleryBasePath'); ?>assets/img/loading.gif" alt="Generating Images... (loading animation)" /></div>
		<p id="redirect-text">This page will continue automatically.</p>
		<p id="explanation-text">If for some reason it does not, click the link below.</p>
		
		<?php
		if ($currentStep <= 2) {
			echo '<p id="continue-link-paragraph">Step ', $nextStep, ': <a href="#null" id="continue-init-link" onClick="setPageLoading(\'', self::__get('selectedAlbum'), '\',\'', $nextStep, '\');">', $continueLinkText, '</a></p>';
		} else {
			echo '<p id="continue-link-paragraph"><a href="#null" id="continue-init-link" onClick="window.location=\'', self::__get('baseStartPage'), '?selectedAlbum=', self::__get('selectedAlbum'), '\';">', $continueLinkText, '</a></p>';
		}
		?>
		</div>
		<script type="text/javascript">
			function setPageLoading(thisAlbum, thisStep) {
				$('#progressbar').css('display','block');
				$("#explanation-text").html("Please do not use your browser's back or refresh buttons at this time.");
				$("#h2-status,#redirect-text,#continue-link-paragraph").html('');
				$.ajax({
					type: "GET",
					url: "<?php echo self::__get('webleryBasePath'); ?>weblery.php",
					data: "selectedAlbum="+thisAlbum+"&step="+thisStep,
					success: function(msg){
						handleSuccess(msg);
					}
				});
			}
		
			$(document).ready(function(){ $("#continue-init-link").click(); });
			
			function handleSuccess(msg) {
				$("#weblery-content").html(msg);
				alert('finished loading weblery;');
				$("#continue-init-link").click();
			}
		</script>
	<?php

		umask($oldUmask);

echo getMemoryInMb() . "<br />";
exit;

	} // End initializeAlbum method

	//Returns an array of the albums in the weblery
	public function getAlbumArray() {
		$directoryArray = array();

		if ($galleryHandle = opendir(self::__get('initGalleryBasePath'))) {
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
	public function getAlbumList() {
		$tempDirectoryArray = self::getAlbumArray();
		$dirCount = 1;
		$albumList = "";

		foreach ($tempDirectoryArray as $dirVal) {
			$selected = "";
			$currentSelectedAlbum = self::__get('selectedAlbum');
			if ((isset($currentSelectedAlbum) && $currentSelectedAlbum == $dirVal) || ($dirCount == 1 && !isset($currentSelectedAlbum))) {
				$selected = " class=\"selected\"";
			}
			$albumList .= "<li" . $selected . "><a href=\"?selectedAlbum=" . $dirVal . "\">" . str_replace("_"," ",$dirVal) . "</a></li>\n";
			$dirCount += 1;
		}

		return $albumList;
	} // End getAlbumList method

	//Returns an array of photos in an album
	public function getPhotoArray() {
		$selectedAlbumPath = self::__get('selectedAlbumPath');
		$albumArray = array();

		if ($albumHandle = opendir($selectedAlbumPath)) {
			$albumArray = self::dirsearch($selectedAlbumPath,'/[.](jpg|jpeg|png)$/i',0,0);
			closedir($albumHandle);
		} else { die("Error opening selected album directory"); }

		if (count($albumArray) <= 0) { die("There are no photos in the selected album"); }

		if (confReverseSort) rsort($albumArray);
		else sort($albumArray);

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
				$currentThumbPath = self::__get('selectedAlbumCachePath') . '/' . "tn_" . md5($albumVal);
				$currentImagePath = self::__get('selectedAlbumCachePath') . '/' . self::__get('mainImageSize') . "_" . md5($albumVal);
				$currentOriginalPath = self::__get('selectedAlbumPath') . '/' . $albumVal;
				$previewImagePath = self::__get('selectedAlbumCachePath') . '/' . (self::__get('mainImageSize')/2) . "_" . md5($albumVal);
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
				$currentThumbPath = self::__get('selectedAlbumCachePath') . '/' . "tn_" . md5($albumVal);
				$currentImagePath = self::__get('selectedAlbumCachePath') . '/' . self::__get('mainImageSize') . "_" . md5($albumVal);
				$currentOriginalPath = self::__get('selectedAlbumPath') . '/' . $albumVal;
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
		if (PHP_VERSION < 5) {
			$albumList = $this->getAlbumList();
			$selectedAlbum = $this->__get('selectedAlbum');
			$selectedAlbumPath = $this->__get('selectedAlbumPath');
			$imageBasePath = $this->__get('imgBasePath');
			$selectedAlbumCachePath = $this->__get('selectedAlbumCachePath');
			$mainImageSize = $this->__get('mainImageSize');
		} else {
			$albumList = self::getAlbumList();
			$selectedAlbum = self::__get('selectedAlbum');
			$selectedAlbumPath = self::__get('selectedAlbumPath');
			$imageBasePath = self::__get('imgBasePath');
			$selectedAlbumCachePath = self::__get('selectedAlbumCachePath');
			$mainImageSize = self::__get('mainImageSize');
		}

		require_once(self::__get('layoutFile')); ?>
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

	public function regenThumbs($regenType, $currentAlbumCachePath) {
		$currentAlbum = self::__get('selectedAlbum');

		if (baseName($_SERVER['SCRIPT_NAME']) == 'weblery.php') {
			$currentBasePath = self::__get('initGalleryBasePath');
		} else {
			$currentBasePath = self::__get('galleryBasePath');
		}

		if (isset($currentAlbum) && strlen($currentAlbum)) {
			$imagefolder=$currentBasePath.'/'.$currentAlbum;
			$thumbsfolder=$currentAlbumCachePath;
			$pics=self::dirsearch($imagefolder,'/[.](jpg|jpeg|png)$/i',0,0);

echo "before pic loop regenThumbs", getMemoryInMb(), "<br />";
			if ($pics[0]!="") {
				foreach ($pics as $p) {
					$currentImagePath = $imagefolder.'/'.$p;
					set_time_limit(20);
					switch ($regenType) {
						case (self::__get('mainImageSize')/2) :
							$s320DestPath = $thumbsfolder.'/'.(self::__get('mainImageSize')/2)."_".md5($p);
							if (!is_file($s320DestPath)) {
								self::resizeImage($currentImagePath,$s320DestPath,(self::__get('mainImageSize')/2));
							}
							break;
						case self::__get('mainImageSize') :
							$s640DestPath = $thumbsfolder.'/'.self::__get('mainImageSize')."_".md5($p);
							if (!is_file($s640DestPath)) {
								self::resizeImage($currentImagePath,$s640DestPath,self::__get('mainImageSize'));
							}
							break;
						default :
							$thumbDestPath = $thumbsfolder.'/'."tn_".md5($p);
							if (!is_file($thumbDestPath)) {
								self::createthumb($currentImagePath,$thumbDestPath,self::__get('defaultThumbWidth'),self::__get('defaultThumbHeight'),1);
							}
							$otherType = 'thumbnails';
							break;
					}
				}
			}
echo "after pic loop regenThumbs", getMemoryInMb(), "<br />";

			if (isset($otherType) && $otherType = 'thumbnails') { $regenType = $otherType; }
		}
	} // End Regen Thumbs Method

	/* Creates a resized image */
	protected function createthumb($name,$filename,$new_w,$new_h,$forceSquare) {
echo "start Create Thumb", getMemoryInMb(), "<br />";
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
		fastimagecopyresampled($dst_img,$src_img,0,0,$cropX,$cropY,$thumb_w,$thumb_h,$old_x,$old_y); 
		if (preg_match("/(.png)/",$name)) {
			imagepng($dst_img,$filename); 
		} else {
			imagejpeg($dst_img,$filename); 
		}
echo "before destroy Create Thumb", getMemoryInMb(), "<br />";
		imagedestroy($dst_img);
		imagedestroy($src_img);
echo "after destroy Create Thumb", getMemoryInMb(), "<br />";
	} // End Create Thumb Method

	protected function resizeImage($name,$filename,$new_size) {
echo "start Resize Image", getMemoryInMb(), "<br />";
		if (preg_match("/(.jpg|.jpeg)$/i",$name)){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/(.png)$/i",$name)){$src_img=imagecreatefrompng($name);}
echo "after imagecreatefromjpeg Resize Image", getMemoryInMb(), "<br />";
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

echo "before ImageCreateTrueColor Resize Image", getMemoryInMb(), "<br />";
		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
echo "before fastimagecopyresampled Resize Image", getMemoryInMb(), "<br />";
		fastimagecopyresampled($dst_img,$src_img,0,0,$cropX,$cropY,$thumb_w,$thumb_h,$old_x,$old_y); 
		if (preg_match("/(.png)$/",$name)) {
			imagepng($dst_img,$filename); 
		} else {
			imagejpeg($dst_img,$filename); 
		}
echo "before destroy Resize Image", getMemoryInMb(), "<br />";
		imagedestroy($dst_img);
		imagedestroy($src_img);
echo "after destroy Resize Image", getMemoryInMb(), "<br />";
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
	protected function dirsearch($dir,$filter='/.*/',$maxdepth=100,$mindepth=0,$level=0,$basedir='') {
		// prevent excessive recursion
		if ( $level >= 100 || $level > $maxdepth ) return array();

		// open the specified directory, return an empty array if opendir() fails
		if ( ($handle = opendir($dir)) === false ) return array();

		// we'll store the matching filenames here
		$files = array();

		// cycle through the files in the directory
		while (($file = readdir($handle)) !== false ) {

			// skip files that start with a .
			if ( preg_match('/^[.\_]/', $file) ) continue;

			// if the file is a directory, recurse into it
			if ( is_dir($dir.'/'.$file) ) {
				foreach ( self::dirsearch($dir.'/'.$file, $filter, $maxdepth, $mindepth, $level+1,"$file/") as $f) {
					array_push($files,$f);
				}
			}

			// add files that match the filter regex
			if ( is_file($dir.'/'.$file) && preg_match($filter, $file) ) {
				// only add the files if we've recursed greater than or equal to mindepth
				if ( $level >= $mindepth ) array_push($files,$basedir.$file);
			}
		}

		closedir($handle);

		return $files;
	} // End dirSearch Method

	// ------------ lixlpixel recursive PHP functions -------------
	// recursive_remove_directory( directory to delete, empty )
	// expects path to directory and optional TRUE / FALSE to empty
	// ------------------------------------------------------------
	protected function recursiveRemoveDirectory($directory, $empty=FALSE) {
		if (substr($directory,-1) == '/') $directory = substr($directory,0,-1);
		if (!file_exists($directory) || !is_dir($directory)) {
			return FALSE;
		} elseif (is_readable($directory)) {
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle))) {
				if($item != '.' && $item != '..') {
					$path = $directory.'/'.$item;
					if (is_dir($path)) {
						recursiveRemoveDirectory($path);
					} else {
						unlink($path);
					}
				}
			}
			closedir($handle);
			if($empty == FALSE) {
				if(!rmdir($directory)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}

} //End weblery class 

//Extra utility functions
function startTimer() {
	$stimer = explode( ' ', microtime() );
	$stimer = $stimer[1] + $stimer[0];

	return $stimer;
}

function endTimer($nameOfTimedSection, $beginTimer) {
	$etimer = explode( ' ', microtime() );
	$etimer = $etimer[1] + $etimer[0];
	echo '<p style="margin:auto; text-align:center">';
	printf( $nameOfTimedSection. ": <b>%f</b> seconds.", ($etimer-$beginTimer) );
	echo '</p>';
}

function fastimagecopyresampled (&$dstImage, $srcImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = confImageQuality) {
  // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
  // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
  // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
  // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
  //
  // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
  // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
  // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
  // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
  // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
  // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
  // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

  if (empty($srcImage) || empty($dstImage) || $quality <= 0) { return false; }
  if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
    $temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
    imagecopyresized ($temp, $srcImage, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
    imagecopyresampled ($dstImage, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
	imagedestroy ($temp);
  } else {
  	imagecopyresampled ($dstImage, $srcImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
  }
  return true;
}

?>

<script type="text/javascript" src="<?php echo confWebleryBasePath; ?>assets/js/jquery-1.3.2.min.js"></script>

<?php 
echo "start: ", getMemoryInMb(), "<br />";
//Instantiate the gallery
$weblery = new weblery();

if (!($weblery->__get('stillInitializing'))) { ?>
	<link type="text/css"  rel="stylesheet" href="<?php echo confWebleryBasePath; ?>assets/js/jqueryUI/css/smoothness/jquery-ui-1.7.1.custom.css" />
	<link type="text/css" rel="stylesheet"  href="<?php echo confWebleryBasePath; ?>assets/css/weblery.css" />
	<?php $weblery->displayWeblery();
} ?>

<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jqueryUI/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/customWeblery.js"></script>

<?php if (!($weblery->__get('stillInitializing'))) { ?>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jquery.exif.js"></script>
	<?php if (confEnablePreloadImages) { ?>
		<script type="text/javascript">$(document).ready(function(){ $.preloadImages(mainImageSize,thumbArray,originalArray); });</script>
	<?php }
} ?>
</div>
