<?php
//Author: Kevin Jones
//Email: kevin@weblery.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 11/04/2011
//Copyright: KJW3 2011
//License: weblery/License.txt
//Manual: weblery/manual.pdf
//
//Note: You should find no need to edit this file
//
//Configuration Parameters are set in weblery/configuration.php
//Please read over the manual.pdf file to learn about configuration settings
?>

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
	var $enablePreview = true;
	var $enableWebleryLink = true;
	var $start = 0;
	var $selectedAlbum = '';

	function weblery() {
		$this->__set('webleryBasePath',confWebleryBasePath); //Directory where weblery.php is located
		$this->__set('galleryBasePath',$this->__get('webleryBasePath') . confGalleryBasePath); //Directory where weblery albums are located

		$this->__set('initGalleryBasePath',$this->__get('galleryBasePath')); //Directory where weblery.php is located
		$this->__set('albumCachePath',$this->__get('webleryBasePath') . 'assets/album_cache'); //Directory where weblery album images are created and stored
		if (baseName($_SERVER['SCRIPT_NAME']) == 'webleryphp4.php') { //Needed for album initialization
			$this->__set('initGalleryBasePath',confGalleryBasePath);
			$this->__set('albumCachePath','assets/album_cache/');
		}

		$this->__set('imgBasePath',$this->__get('webleryBasePath') . 'assets/img/'); //Directory where weblery images is located
		$this->__set('layoutFile',$this->__get('webleryBasePath') . 'assets/layout/'.confLayoutFile);
		$this->__set('baseStartPage',confBaseStartPage);
		$this->__set('defaultThumbWidth',confDefaultThumbWidth);
		$this->__set('defaultThumbHeight',confDefaultThumbWidth);
		$this->__set('mainImageSize',confMainImageSize);
		$this->__set('enablePreview',confEnablePreview);
		$this->__set('enableWebleryLink',confEnableWebleryLink);

		$tempAlbumArray = $this->getAlbumArray();

		if (count($tempAlbumArray)) {
			$this->__set('selectedAlbum',array_shift(array_values($tempAlbumArray)));

			if (isset($_GET['selectedAlbum']) && in_array($_GET['selectedAlbum'],$tempAlbumArray) && strlen($_GET['selectedAlbum']) > 0) {
				if(strpos($_GET['selectedAlbum'], "'") > -1) die("Please remove any apostrophes from the album name.");
				$this->__set('selectedAlbum',$_GET['selectedAlbum']);
			}

			$this->__set('start',0);
			if (isset($_GET['start']) && strlen($_GET['start']) > 0)
				$this->__set('start',$_GET['start']);

			$this->__set('selectedAlbumPath',$this->__get('initGalleryBasePath').'/'.$this->__get('selectedAlbum'));
			$this->__set('selectedAlbumCachePath',$this->__get('albumCachePath').'/'.$this->__get('selectedAlbum'));

			$this->cleanAlbumCache();

			$stepCheck = isset($_GET['step']) && (strlen($_GET['step']) > 0 && is_numeric($_GET['step']));

			if ($this->isInitialized() && !$stepCheck) {
				$this->__set('stillInitializing',false);
			} else {
				$this->__set('stillInitializing',true);

				$this->__set('initStep',0);
				if ($stepCheck) $this->__set('initStep',$_GET['step']);

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
		//Go through and remove any cached images that are no longer needed
		if (is_dir($this->__get('selectedAlbumCachePath'))) {
			$tempDirectoryArray = $this->getPhotoArray();
			$albumCacheArray = $this->dirsearch($this->__get('selectedAlbumCachePath'),'/^.+_/i',0,0);
			$files = array();
			foreach ($tempDirectoryArray as $currentImage) {
				$fileExtension = pathinfo($currentImage, PATHINFO_EXTENSION);
				if (in_array("tn_" . md5($currentImage).'.'.$fileExtension, $albumCacheArray)) $files[] = "tn_" . md5($currentImage).'.'.$fileExtension;
				if (in_array(($this->__get('mainImageSize')/2)."_" . md5($currentImage).'.'.$fileExtension, $albumCacheArray)) $files[] = ($this->__get('mainImageSize')/2)."_" . md5($currentImage).'.'.$fileExtension;
				if (in_array($this->__get('mainImageSize')."_" . md5($currentImage).'.'.$fileExtension, $albumCacheArray)) $files[] = $this->__get('mainImageSize')."_" . md5($currentImage).'.'.$fileExtension;
			}
			$filesToDelete = array_diff($albumCacheArray, $files);
			foreach ($filesToDelete as $fileToDelete) {
				unlink($this->__get('selectedAlbumCachePath') . '/' . $fileToDelete);
			}
		}

		//Go through and remove any album cache directory for albums that no longer exist
		$albumCacheDirectoryArray = array();
		$albumCacheDirs = array();

		if ($galleryHandle = opendir($this->__get('albumCachePath'))) {
			while (false !== ($file = readdir($galleryHandle))) {
		        if (!(preg_match("/^[._]/", $file))) {
					$albumCacheDirectoryArray[] = $file;
				}
		    }
			closedir($galleryHandle);
		} else { die("Error opening album cache directory"); }

		foreach ($albumCacheDirectoryArray as $currentAlbumCache) {
			if (is_dir($this->__get('initGalleryBasePath').'/'.$currentAlbumCache)) $albumCacheDirs[] = $currentAlbumCache;
		}

		$albumCacheToDelete = array_diff($albumCacheDirectoryArray, $albumCacheDirs);

		foreach ($albumCacheToDelete as $dirToDelete) {
			$rmDirResult = $this->recursiveRemoveDirectory($this->__get('albumCachePath')."/".$dirToDelete);
		}
	}

	function isInitialized() {
		if (is_dir($this->__get('selectedAlbumCachePath'))) {
			$tempDirectoryArray = $this->getPhotoArray();
			$albumCacheArray = $this->dirsearch($this->__get('selectedAlbumCachePath'),'/^(tn_|320_|640_)/i',0,0);
			$checkCount = 3;

			if (count($albumCacheArray) == ($checkCount * count($tempDirectoryArray))) return true;
			else return false; //Not enough images in the cache folder
		} else return false; //No cache folder exists for this album
	} // End isInitialized method

	function initializeAlbum($currentStep) {
		$oldUmask = umask(0);
		if ( !(is_dir($this->__get('selectedAlbumCachePath'))) ) {
			mkdir($this->__get('selectedAlbumCachePath') , 0777) or
				die("Failed to create directory. Please make sure that " . $this->__get('albumCachePath') . " is readable and writable by the web server");
		}

		$numberOfSteps = 3;
		$nextStep = $currentStep + 1;

		switch($currentStep) {
			case 1 :
				$this->regenThumbs(($this->__get('mainImageSize')/2),$this->__get('selectedAlbumCachePath'));
				$continueLinkText = "Generate ".$this->__get('mainImageSize')." width images";
				break;
			case 2 :
				$this->regenThumbs($this->__get('mainImageSize'),$this->__get('selectedAlbumCachePath'));
				$continueLinkText = "Generate thumbnail images";
				break;
			case 3 :
				$this->regenThumbs('',$this->__get('selectedAlbumCachePath'));
				$continueLinkText = "View your new album";
				break;
			default :
				$continueLinkText = "Generate ".($this->__get('mainImageSize')/2)." width images";
				break;
		}

		if ( $this->__get('stillInitializing') && $currentStep == 0) echo '<style>#init-container{margin:10px;}</style><div id="init-content">'; ?>
		<div id="init-container">
			<h1>Album Initialization</h1>
			
			<?php if ($currentStep < 1 || $currentStep > 3) { ?>
				<h2 id="h2-status" style="font-size:small;">This album first needs to be initialized</h2>
			<?php } else { ?>
				<h2 id="h2-status" style="font-size:small;">Step <?php echo $currentStep; ?> of <?php echo $numberOfSteps; ?> Complete</h2>
			<?php } ?>
			
			<div id="progressbar" style="display:none;"><img id="loading-img" src="<?php echo $this->__get('webleryBasePath'); ?>assets/img/loading.gif" alt="Generating Images... (loading animation)" /></div>
			<p id="redirect-text">This page will continue automatically.</p>
			<p id="explanation-text">If for some reason it does not, click the link below.</p>
			
			<?php
			if ($currentStep <= 2) {
				echo '<p id="continue-link-paragraph">Step ', $nextStep, ': <a href="#null" id="continue-init-link" onClick="setPageLoading(\'', $this->__get('selectedAlbum'), '\',\'', $nextStep, '\');return false;">', $continueLinkText, '</a></p>';
			} else {
				echo '<p id="continue-link-paragraph"><a href="#null" id="continue-init-link" onClick="window.location=\'', $this->__get('baseStartPage'), '?selectedAlbum=', $this->__get('selectedAlbum'), '\';return false;">', $continueLinkText, '</a></p>';
			}
			?>
		</div>
		<?php if ($this->__get('stillInitializing') && $currentStep == 0) echo '</div>'; ?>
		
		<script type="text/javascript">
			function setPageLoading(thisAlbum, thisStep) {
				$('#progressbar').css('display','block');
				$("#explanation-text").html("Step " + thisStep + " of <?php echo $numberOfSteps; ?>: " + $("#continue-init-link").html() + "<br />Please do not use your browser's back or refresh buttons at this time.");
				$("#h2-status,#redirect-text,#continue-link-paragraph").html('');
				$.ajax({
					type: "GET",
					url: "<?php echo $this->__get('webleryBasePath'); ?>webleryphp4.php",
					cache: false,
					data: "selectedAlbum="+thisAlbum+"&step="+thisStep,
					success: function(msg){
						handleSuccess(msg);
					}
				});
			}
		
			function handleSuccess(msg) {
				$("#init-content").html(msg);
				$("#continue-init-link").click();
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
			$dirLoopCnt = 0;
			while (false !== ($file = readdir($galleryHandle))) {
		        if (!(preg_match("/^[._]/", $file))) {
					$dateModified = filemtime($this->__get('initGalleryBasePath')."/".$file);
					$directoryArray[$dateModified."_".$dirLoopCnt] = $file;
				}
				$dirLoopCnt++;
		    }
			closedir($galleryHandle);
		} else { die("Error opening gallery directory"); }

		krsort($directoryArray);

		return $directoryArray;
	} // End getAlbumArray method

	//Returns a list of links for the albums in the weblery
	function getAlbumList() {
		$tempDirectoryArray = $this->getAlbumArray();
		$dirCount = 1;
		$albumList = "";

		foreach ($tempDirectoryArray as $key=>$dirVal) {
			$selected = "";
			$currentSelectedAlbum = $this->__get('selectedAlbum');
			if ((isset($currentSelectedAlbum) && $currentSelectedAlbum == $dirVal) || ($dirCount == 1 && !isset($currentSelectedAlbum))) {
				$selected = " class=\"selected\"";
			}
			$albumList .= "<li" . $selected . " style=\"font-size:small;\"><a href=\"?selectedAlbum=" . $dirVal . "\">" . str_replace("_"," ",$dirVal) . "</a></li>\n";
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
			closedir($albumHandle);
		} else { die("Error opening selected album directory"); }

		if (count($albumArray) <= 0) { die("There are no photos in the selected album"); }

		if (confReverseSort) rsort($albumArray);
		else sort($albumArray);

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
			$fileExtension = pathinfo($albumVal, PATHINFO_EXTENSION);
			if ($albumCount >= $leftThumbStart && $albumCount <= $rightThumbEnd) {
				$currentThumbPath = $this->__get('selectedAlbumCachePath') . '/' . "tn_" . md5($albumVal).'.'.$fileExtension;
				$currentImagePath = $this->__get('selectedAlbumCachePath') . '/' . $this->__get('mainImageSize') . "_" . md5($albumVal).'.'.$fileExtension;
				$currentOriginalPath = $this->__get('selectedAlbumPath') . '/' . $albumVal;
				$previewImagePath = $this->__get('selectedAlbumCachePath') . '/' . ($this->__get('mainImageSize')/2) . "_" . md5($albumVal).'.'.$fileExtension;
			}
			if ($albumCount >= $rightThumbStart && $albumCount <= $rightThumbEnd) {
				$rightThumbList .= '<li><img src="' . $currentThumbPath . '" style="width:' . $this->__get('defaultThumbWidth') . 'px;border: 1px solid #727375;" ';
				$rightThumbList .= 'alt="' . $albumVal . '" onclick="javascript:resetCurrentImage(\'' . $currentImagePath . '\', \'' . $currentOriginalPath . '\');';
				if($this->__get('enablePreview')) $rightThumbList .= 'setTimeout(\'hidePreviewImage();\', 350);';
				$rightThumbList .= '"';
				if($this->__get('enablePreview')) $rightThumbList .= ' onmouseover="javascript:previewImage(\'' . $previewImagePath . '\');"';
				$rightThumbList .= ' /></li>' . "\n";
			}
			if ($albumCount >= $leftThumbStart && $albumCount <= $leftThumbEnd) {
				$leftThumbList .= '<li><img src="' . $currentThumbPath . '" style="width:' . $this->__get('defaultThumbWidth') . 'px;border: 1px solid #727375;" ';
				$leftThumbList .= 'alt="' . $albumVal . '" onclick="javascript:resetCurrentImage(\'' . $currentImagePath . '\', \'' . $currentOriginalPath . '\');';
				if($this->__get('enablePreview')) $leftThumbList .= 'setTimeout(\'hidePreviewImage();\', 350);';
				$leftThumbList .= '"';
				if($this->__get('enablePreview')) $leftThumbList .= ' onmouseover="javascript:previewImage(\'' . $previewImagePath . '\');"';
				$leftThumbList .= ' /></li>' . "\n";
			}
			$albumCount += 1;
		}

		return $leftThumbList . "|" . $rightThumbList;
	} // End getPhotoList method

	function getSetLinks($selectedAlbum,$albumArray,$imageId) {
		$numberOfSets = ceil(count($albumArray)/16);
		$allSetLinks = "";

		if ($numberOfSets > 1) {
			for ($i=0;$i<$numberOfSets;$i++) {
				$setLink = "";
				$selectedStyle = "";
				if ($i == round($imageId/16)) {
					$selectedStyle = 'color:black;';
				}

				$urlStartNumber = $i+($i*15);
				$startNumber = ($urlStartNumber)+1;

				$setLink = '<a href="?selectedAlbum=' . urlencode($selectedAlbum) . '&amp;start=' . $urlStartNumber . '" style="' . $selectedStyle . 'padding-right:5px;">' . $startNumber . '-';

				if ($i == ($numberOfSets-1)) {
					$lastStartNumber = $urlStartNumber;
					$setLink .= count($albumArray) . '</a> ';
				} else {
					$setLink .= ($urlStartNumber)+16 . '</a> ';
				}

				$allSetLinks .= $setLink;
			}
		}

		return $allSetLinks;
	}

	function displayWeblery() {
		$currentAlbumArray = $this->getPhotoArray();
		$currentStart = $this->__get('start');
		$thumbList = preg_split('/\|/', $this->getPhotoList());
		

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
				$fileExtension = pathinfo($albumVal, PATHINFO_EXTENSION);
				$currentThumbPath = $this->__get('selectedAlbumCachePath') . '/' . "tn_" . md5($albumVal).'.'.$fileExtension;
				$currentImagePath = $this->__get('selectedAlbumCachePath') . '/' . $this->__get('mainImageSize') . "_" . md5($albumVal).'.'.$fileExtension;
				$currentOriginalPath = $this->__get('selectedAlbumPath') . '/' . $albumVal;
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
		$setLinks = $this->getSetLinks($selectedAlbum,$currentAlbumArray,$currentImageId);
		$webleryLink = $this->getWebleryLink();

		require_once($this->__get('layoutFile')); ?>
		<script type="text/javascript">
			function changeImage(arrayPosition) {
				if (arrayPosition == thumbArray.length || arrayPosition == 0 && <?php echo $currentImageId; ?> != 0) {
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
					document.getElementById('prev-image-link').onclick = function(){changeImage(prevArrayPosition);return false;};
					document.getElementById('next-image-link').onclick = function(){changeImage(nextArrayPosition);return false;};
				}
			}
			
			function resetCurrentImage(imagePath, originalImagePath) {
				document.getElementById('current-image-img').src = imagePath;
				document.getElementById('current-image-original-img').src = originalImagePath;
				document.getElementById('view-original').href = originalImagePath;
				
				var prevArrayPosition = -1;
				var nextArrayPosition = 1;
				for(var i = 0; i < thumbArray.length; i++) {
					if(thumbArray[i] === imagePath) {
						prevArrayPosition = i - 1;
						if (prevArrayPosition < 0) { prevArrayPosition = thumbArray.length-1; }
						nextArrayPosition = i + 1;
						if (nextArrayPosition >= thumbArray.length) { nextArrayPosition = 0; }
					}
				}
				document.getElementById('prev-image-link').onclick = function(){changeImage(prevArrayPosition);return false;};
				document.getElementById('next-image-link').onclick = function(){changeImage(nextArrayPosition);return false;};
			}
			<?php if($this->__get('enablePreview')) { ?>
			function previewImage(imagePath) {
				document.getElementById('preview-image-img').src = imagePath;
				setTimeout( "showPreviewImage();", 500); 
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
			<?php } ?>
		</script>
<?php
	} // End displayWeblery method

	function getWebleryLink() {
		if ($this->__get('enableWebleryLink'))
			return '<a href="http://weblery.com" style="font-size:8pt;" title="Powered by Weblery" target="_blank">Powered by Weblery</a>';
	}

	//Thumbnail Methods

	function regenThumbs($regenType, $currentAlbumCachePath) {
		$currentAlbum = $this->__get('selectedAlbum');

		if (baseName($_SERVER['SCRIPT_NAME']) == 'webleryphp4.php') {
			$currentBasePath = $this->__get('initGalleryBasePath');
		} else {
			$currentBasePath = $this->__get('galleryBasePath');
		}

		if (isset($currentAlbum) && strlen($currentAlbum)) {
			$imagefolder=$currentBasePath.'/'.$currentAlbum."/";
			$thumbsfolder=$currentAlbumCachePath;
			$pics=$this->dirsearch($imagefolder,'/[.](jpg|jpeg|png)$/i',0,0);

			if ($pics[0]!="") {
				foreach ($pics as $p) {
					$currentImagePath = $imagefolder.'/'.$p;
					$fileExtension = pathinfo($p, PATHINFO_EXTENSION);
					set_time_limit(20);
					switch ($regenType) {
						case ($this->__get('mainImageSize')/2) :
							$s320DestPath = $thumbsfolder.'/'.($this->__get('mainImageSize')/2)."_".md5($p).'.'.$fileExtension;
							if (!is_file($s320DestPath)) {
								$this->resizeImage($currentImagePath,$s320DestPath,($this->__get('mainImageSize')/2));
							}
							break;
						case $this->__get('mainImageSize') :
							$s640DestPath = $thumbsfolder.'/'.$this->__get('mainImageSize')."_".md5($p).'.'.$fileExtension;
							if (!is_file($s640DestPath)) {
								$this->resizeImage($currentImagePath,$s640DestPath,$this->__get('mainImageSize'));
							}
							break;
						default :
							$thumbDestPath = $thumbsfolder.'/'."tn_".md5($p).'.'.$fileExtension;
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
		fastimagecopyresampled($dst_img,$src_img,0,0,$cropX,$cropY,$thumb_w,$thumb_h,$old_x,$old_y); 
		if (preg_match("/(.png)/",$name)) {
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
		fastimagecopyresampled($dst_img,$src_img,0,0,$cropX,$cropY,$thumb_w,$thumb_h,$old_x,$old_y); 
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
				foreach ( $this->dirsearch($dir.'/'.$file, $filter, $maxdepth, $mindepth, $level+1,"$file/") as $f) {
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
	function recursiveRemoveDirectory($directory, $empty=FALSE) {
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

function setMemoryForImage( $filename ){
    $imageInfo = getimagesize($filename);
    $MB = 1048576;  // number of bytes in 1M
    $K64 = 65536;    // number of bytes in 64K
    $TWEAKFACTOR = 1.5;  // Or whatever works for you
    $memoryNeeded = round( ( $imageInfo[0] * $imageInfo[1]
                                           * $imageInfo['bits']
                                           * $imageInfo['channels'] / 8
                             + $K64
                           ) * $TWEAKFACTOR
                         );
    //ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
    //Default memory limit is 8MB so well stick with that. 
    //To find out what yours is, view your php.ini file.
    $memoryLimitMB = 8;
	$memoryLimit = $memoryLimitMB * $MB;
    if (function_exists('memory_get_usage') && 
        memory_get_usage() + $memoryNeeded > $memoryLimit) {
        $newLimit = $memoryLimitMB + ceil( ( memory_get_usage()
                                            + $memoryNeeded
                                            - $memoryLimit
                                            ) / $MB
                                        );
        ini_set( 'memory_limit', $newLimit . 'M' );
        return true;
    } else return false;
}
?>

<script type="text/javascript" src="<?php echo confWebleryBasePath; ?>assets/js/jquery-1.6.1.min.js"></script>

<?php 
//Instantiate the gallery
$weblery = new weblery();

if (!($weblery->__get('stillInitializing'))) { ?>

	<div id="weblery-content">	

		<link type="text/css"  rel="stylesheet" href="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jqueryUI/css/smoothness/jquery-ui-1.7.1.custom.css" />

		<style type="text/css">
			.clear { clear: both; }
			
			.float-left-container { float: left; }
			
			.thumbnail-container { z-index:100; }
			
			.thumbnail-container li { display: block; margin: 0 0 2px 0; }
			
			img { border:none; }
			
			#current-image { position:relative; }
			
			#preview-image { display: none; position:absolute; top:20px; left:0; width:<?php echo confMainImageSize + 10; ?>px; text-align:center; z-index: 1000; }
			#preview-image-img { border: 4px solid #f3f3f3; }
			
			/*** Dialog Override ***/
			.ui-dialog .ui-dialog-titlebar { padding: 3px 2px 2px 2px; position: relative;  }
			.ui-dialog .ui-dialog-title { float: left; margin: 0; font-size:large; } 
			.ui-dialog .ui-dialog-buttonpane { text-align: left; border-width: 1px 0 0 0; background-image: none; margin: .1em 0 0 0; padding: .1em .1em .1em .1em; }
			.ui-dialog .ui-dialog-buttonpane button { float: right; margin: .2em .1em .1em 0; cursor: pointer; padding: .1em .1em .1em .1em; line-height: 1em; width:auto; overflow:visible; }
		</style>

		<?php $weblery->displayWeblery(); ?>

	</div>
	
<?php } ?>

<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jqueryUI/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/customWeblery.js"></script>

<?php if (!($weblery->__get('stillInitializing'))) { ?>
<script type="text/javascript" src="<?php echo $weblery->__get('webleryBasePath'); ?>assets/js/jquery.exif.js"></script>
	<?php if (confEnablePreloadImages) { ?>
		<script type="text/javascript">$(document).ready(function(){ $.preloadImages(mainImageSize,thumbArray,originalArray,<?php echo confEnablePreloadImages; ?>,<?php if (isset($_GET['start']) && is_numeric($_GET['start'])) { echo $_GET['start']; } else { echo 0; } ?>);});</script>
	<?php }
} ?>
