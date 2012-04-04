<?php
//Author: Kevin Jones
//Email: kevin@weblery.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 06/11/2009
//Copyright: KJW3, LLC 2011
//License: ./License.txt
//Manual: ./manual.pdf
//
//Note: This is layout2 a default layout provided
//with Weblery.  You can copy this code and paste
//into another php file that you create in the
//./assets/layout folder.  Then change the confLayoutFile
//variable in the ./configuration.php file
//to the name of your new file.
//
//Configuration Parameters are set in configuration.php
?>

<style type="text/css">
/*** Basic Tab CSS ***/
.basictab{ margin-top:0;margin-left:0;margin-right: 10px;margin-bottom:0;padding:3px 0;border-bottom:1px solid #727375;list-style-type: none;font-weight: bold;font-size: x-small;text-align: left; }
.basictab li{ position:relative;display:inline;margin:0; }
.basictab li a{ margin-right:1px; padding:3px 7px; _padding:4px 7px;background-color:#fff;border:1px solid #727375;border-bottom:none;font-size:10pt;color:#727375;text-decoration:none; }
.basictab li a:visited{ color:#727375; }
.basictab li a:hover{ background-color:#DCE7F1;color:black; }
.basictab li a:active{ color:black; }
.basictab li.selected a{ position:relative;top:1px;padding-top:4px; _padding-top:5px;background-color:#DCE7F1;color:black; }
#basictab-content-container {margin-right:10px;margin-bottom:5px;background-color:#DCE7F1;border-top:none;border-left:1px solid #727375;border-right:1px solid #727375;border-bottom:1px solid #727375;color:#727375; }
/*** End Basic Tab CSS ***/
</style>

<div id="layout" style="width:<?php echo confMainImageSize + 80 + ( confDefaultThumbWidth * 2); ?>px;">
	<ul id="basictab-ul" class="basictab" onmouseover="hidePreviewImage();">
		<?php echo $albumList; ?>
	</ul>
	<div id="basictab-content-container">
		<div class="float-left-container" style="text-align:left;margin: 0 10px;">
			<ul class="thumbnail-container" style="list-style:none;margin:0;padding:0;">
				<li style="margin-top:5px;"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="prev-image-link" onclick="javascript:changeImage(<?php echo $prevThumbPosition; ?>);return false;"><img src="<?php echo $imageBasePath; ?>skipBackward.png" style="border:none;" alt="Previous Image" /></a></li>
				<?php echo $thumbList[0]; ?>
			</ul>
		</div>
		
		<div class="float-left-container" style="text-align:center;">
			<div id="current-image-extras"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>>
				<div id="page-sets" style="margin:5px 0;">
				<?php echo $setLinks; ?>
				</div>
				<div id="photo-detail"></div>
				<div id="playHtml" style="display:none;">Play</div>
				<div id="pauseHtml" style="display:none;">Pause</div>
				<a href="#null" id="slideshow-link" style="margin:0 10px;" slideshowStatus="<?php echo $slideshowLinkText; ?>"><?php echo $slideshowLinkText; ?></a>
				<a href="#null" id="slideshow-link" style="padding-right:20px;" slideshowStatus="Play"><script type="text/javascript">$(document).ready(function() { $("#slideshow-link").html($("#playHtml").html()); });</script></a>
				<a href="#null" id="photo-detail-link" style="padding-right:20px;">Details</a>
				<a href="<?php echo $selectedAlbumPath . '/' . $currentAlbumArray[$currentImageId]; ?>" id="view-original" target="_blank">Original</a>
			</div>

			<div id="current-image"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="width:<?php echo confMainImageSize + 12; ?>px;margin:10px 0;">
				<?php if(confEnablePreview) { ?>
					<div id="preview-image" onmouseover="javascript:hidePreviewImage();"><img id="preview-image-img" src="<?php echo $selectedAlbumCachePath . '/' . ($mainImageSize/2) . "_" . md5($currentAlbumArray[0]) . '.' . pathinfo($currentAlbumArray[0], PATHINFO_EXTENSION); ?>" style="margin:0 auto;border: 4px solid #fff;" alt="Preview Image" /></div>
				<?php } ?>
				<img id="current-image-img" src="<?php echo $selectedAlbumCachePath . '/' . $mainImageSize . "_" . md5($currentAlbumArray[$currentImageId]) . '.' . pathinfo($currentAlbumArray[$currentImageId], PATHINFO_EXTENSION); ?>" style="margin:0 auto 5px auto;border: 4px solid #f3f3f3;" alt="Current Image" />
				<br />
				
			</div>

			<div id="current-image-original"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="overflow:hidden;"><img id="current-image-original-img" src="<?php echo $selectedAlbumPath . '/' . $currentAlbumArray[$currentImageId]; ?>" style="display:none;" alt="Current Image" /></div>
		</div>

		<div class="float-left-container" style="text-align:left;margin:0 10px;">
			<ul class="thumbnail-container" style="list-style:none;margin:0;padding:0;">
				<li style="margin-top:5px;"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="next-image-link" onclick="javascript:changeImage(<?php echo $currentImageId + 1; ?>);return false;"><img src="<?php echo $imageBasePath; ?>skipForward.png" style="border:none;" alt="Next Image" /></a></li>
				<?php if (strlen($thumbList[1])) {
					echo $thumbList[1];
				} ?>
			</ul>
		</div>
		<br class="clear" />
	</div>
	<div style="font-size:xx-small;text-align:center;"><?php echo $webleryLink;?></div>
</div>