<?php
//Author: Kevin Jones
//Email: kevin@weblery.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 11/04/2011
//Copyright: KJW3, LLC 2011
//License: weblery/License.txt
//Manual: weblery/manual.pdf
//
//Note: This is layout1 a default layout provided
//with Weblery.  You can copy this code and paste
//into another php file that you create in the
//weblery/assets/layout folder.  Then change the confLayoutFile
//variable in the weblery/configuration.php file
//to the name of your new layout file.
//
//Configuration Parameters are set in weblery/configuration.php
?>

<div id="layout" style="position:relative;margin:10px 0 10px 0;min-width:<?php echo 150 + confMainImageSize + 80 + ( confDefaultThumbWidth * 2); ?>px;">
	<ul class="float-left-container" style="width:150px;list-style:none;padding-left:5px;"<?php if(confEnablePreview) { ?> onmouseover="hidePreviewImage();"<?php } ?>>
		<li style="font-weight: bold;border-bottom:1px solid black;">Galleries</li>
		<?php echo $albumList; ?>
	</ul>
	<div class="float-left-container" style="position:relative;width:<?php echo confMainImageSize + 70 + ( confDefaultThumbWidth * 2); ?>px;">
		<div class="float-left-container" style="width:100%;text-align:center;">
			<?php echo $setLinks; ?>
			<div id="current-image-extras"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>>
				<div id="photo-detail"></div>
				<span id="previous-image-cell" style="padding-right:10px;"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="prev-image-link" onclick="javascript:changeImage(<?php echo $prevThumbPosition; ?>);return false;" title="Previous Photo"><img src="<?php echo $imageBasePath; ?>media-seek-backward.png" alt="Previous Image" style="border:none;" /></a></span>
				<div id="playHtml" style="display:none;">Play</div>
				<div id="pauseHtml" style="display:none;">Pause</div>
				
				<a href="#null" id="slideshow-link" style="margin:0 10px;" slideshowStatus="<?php echo $slideshowLinkText; ?>"><?php echo $slideshowLinkText; ?></a>
				<a href="#null" id="photo-detail-link" style="padding-right:10px;">Photo Details</a>
				<a href="<?php echo $selectedAlbumPath . '/' . $currentAlbumArray[$currentImageId]; ?>" id="view-original" style="padding-right:20px;" target="_blank">View Original</a>
				<span id="next-image-cell" <?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="next-image-link" onclick="javascript:changeImage(<?php echo $currentImageId + 1; ?>);return false;" title="Next Photo"><img src="<?php echo $imageBasePath; ?>media-seek-forward.png" alt="Next Image" style="border:none;" /></a></span>
			</div>
		</div>
		
		<div class="float-left-container" style="position:relative;text-align:center;">
			<div class="float-left-container" style="text-align:left;margin: 0 10px;">
				<ul class="thumbnail-container" style="list-style:none;margin:0;padding:0;">
					<?php echo $thumbList[0]; ?>
				</ul>
			</div>
			
			<div id="current-image" class="float-left-container"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="width:<?php echo confMainImageSize + 10; ?>px;">
				
				<img id="current-image-img" src="<?php echo $selectedAlbumCachePath . '/' . $mainImageSize . "_" . md5($currentAlbumArray[$currentImageId]) . '.' . pathinfo($currentAlbumArray[$currentImageId], PATHINFO_EXTENSION); ?>" style="border:4px solid #f3f3f3;" alt="Current Image" />

				<?php if(confEnablePreview) { ?>
					<div id="preview-image" onmouseover="javascript:hidePreviewImage();">
						<img id="preview-image-img" src="<?php echo $selectedAlbumCachePath . '/' . ($mainImageSize/2) . "_" . md5($currentAlbumArray[0]) . '.' . pathinfo($currentAlbumArray[0], PATHINFO_EXTENSION); ?>" alt="Preview Image" />
					</div>
				<?php } ?>
				
				<div id="current-image-original"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="position:absolute;top:-5000px;left:-5000px;overflow:hidden;">
					<img id="current-image-original-img" src="<?php echo $selectedAlbumPath . '/' .  $currentAlbumArray[$currentImageId]; ?>" style="visibility:hidden;" alt="Current Image" />
				</div>
				
			</div>
			
			<div class="float-left-container" style="text-align:left;margin-left:10px;">
				<?php if (strlen($thumbList[1])) { ?>
					<ul class="thumbnail-container" style="list-style:none;margin:0;padding:0;">
						<?php echo $thumbList[1]; ?>
					</ul>
				<?php } ?>
			</div>
		</div>
		<br class="clear" />
		<div style="font-size:xx-small;text-align:center;"><?php echo $webleryLink;?></div>
	</div>
	<br class="clear" />
</div>