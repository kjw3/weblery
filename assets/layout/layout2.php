<?php
//Author: Kevin Jones
//Email: kevin.d.jones@gmail.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 06/11/2009
//Copyright: Kevin Jones 2009
//License: ./documentation/License.txt
//
//Note: This is layout2 a default layout provided
//with Weblery.  You can copy this code and paste
//into another php file that you create in the
//./assets/layout folder.  Then change the confLayoutFile
//variable in the ./configuration.php file
//to the name of your new file.
//
//Configuration Parameters are set in configuration.php
//Please read over the documents in the documentation folder
?>

<div class="float-left-container">
	<ul id="basictab-ul" class="basictab" style="margin-top:10px;" onmouseover="hidePreviewImage();">
		<?php echo $albumList; ?>
	</ul>
	<div id="basictab-content-container">
		
		<table style="width:100%;">
			<tr>
				<td valign="top" style="text-align:left;padding-top:1px;padding-left:1px; width:48px;">
					<span id="previous-image-cell"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="prev-image-link" onclick="javascript:changeImage(<?php echo $prevThumbPosition; ?>);return false;"><img src="<?php echo $imageBasePath; ?>skipBackward.png" alt="Previous Image" /></a></span>
					<div class="thumbnail-container">
						<ul>
							<?php echo $thumbList[0]; ?>
						</ul>
						<br class="thumbnailClear" />
					</div>
				</td>
				<td valign="top" style="text-align:center;">
					<div id="current-image-extras"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>>
						<div id="photo-detail"></div>
						<a href="#null" id="slideshow-link" style="padding-right:20px;">Play</a>
						<a href="#null" id="photo-detail-link" style="padding-right:20px;">Photo Details</a>
						<a href="<?php echo $selectedAlbumPath . '/' . $currentAlbumArray[$currentImageId]; ?>" id="view-original" target="_blank">View Original</a>
					</div>
					
					
					<div id="current-image"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="overflow:hidden;">
						<?php if(confEnablePreview) { ?>
						<div id="preview-image" onmouseover="javascript:hidePreviewImage();"><img id="preview-image-img" src="<?php echo $selectedAlbumCachePath . '/' . ($mainImageSize/2) . "_" . md5($currentAlbumArray[0]); ?>" style="margin:0 auto;border: 4px solid #fff;" alt="Preview Image" /></div>
						<?php } ?>
						<img id="current-image-img" src="<?php echo $selectedAlbumCachePath . '/' . $mainImageSize . "_" . md5($currentAlbumArray[$currentImageId]); ?>" style="margin:0 auto;border: 4px solid #fff;" alt="Current Image" /></div>
					<div id="current-image-original"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="overflow:hidden;"><img id="current-image-original-img" src="<?php echo $selectedAlbumPath . '/' . $currentAlbumArray[$currentImageId]; ?>" style="display:none;" alt="Current Image" /></div>
					
					<?php
					if ($numberOfSets > 1) {
						for ($i=0;$i<$numberOfSets;$i++) {
							if ($i == round($currentImageId/16)) {
								$selectedStyle = ' style="color:#09679A;"';
							} else {
								$selectedStyle = "";
							}
							if ($i == ($numberOfSets-1)) {
								$lastStartNumber = $i+($i*15);
								echo '<a href="?selectedAlbum=', urlencode($selectedAlbum), '&amp;start=', $i+($i*15), '"', $selectedStyle, '>[', ($i+($i*15))+1, '-', count($currentAlbumArray), ']</a> ';
							} else {
								echo '<a href="?selectedAlbum=', urlencode($selectedAlbum), '&amp;start=', $i+($i*15), '"', $selectedStyle, '>[', ($i+($i*15))+1, '-', ($i+($i*15))+16, ']</a> ';
							}
						}
					} else { $lastStartNumber = 0; }
					?>
				</td>

				<td valign="top" style="text-align:right;padding-top:1px;padding-right:1px; width:48px;">
					<span id="next-image-cell"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="next-image-link" onclick="javascript:changeImage(<?php echo $currentImageId + 1; ?>);return false;"><img src="<?php echo $imageBasePath; ?>skipForward.png" alt="Next Image" /></a></span>
					<?php if (strlen($thumbList[1])) { ?>
					<div class="thumbnail-container" style="text-align:right;">
						<ul>
							<?php echo $thumbList[1]; ?>
						</ul>
						<br class="thumbnailClear" />
					</div>
					<?php } ?>
				</td>
			</tr>
		</table>
	</div>
</div>