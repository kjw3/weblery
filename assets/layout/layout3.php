<?php
//Author: Kevin Jones
//Email: kevin.d.jones@gmail.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 06/11/2009
//Copyright: Kevin Jones 2009
//License: ./documentation/License.txt
//
//Note: This is layout1 a default layout provided
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
	<div style="width: 1000px;">
		<ul class="float-left-container" style="list-style:none;padding-left:5px;padding-right:5px;"<?php if(confEnablePreview) { ?> onmouseover="hidePreviewImage();"<?php } ?>>
			<li style="font-weight: bold;border-bottom:1px solid black;">Galleries</li>
			<?php echo $albumList; ?>
		</ul>
		<table class="float-left-container">
			<tr>
				<td valign="top" style="text-align:left;padding-top:21px;padding-left:1px; width:48px;">
					
					<div class="thumbnail-container">
						<ul>
							<?php echo $thumbList[0]; ?>
						</ul>
						<br class="thumbnailClear" />
					</div>
				</td>
				<td valign="top" style="text-align:left;">
					
					<div id="current-image-extras"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>>
						<div id="photo-detail"></div>
						<div id="playHtml" style="display:none;"><img src="<?php echo $imageBasePath; ?>media-playback-start.png" alt="Play" /></div>
						<div id="pauseHtml" style="display:none;"><img src="<?php echo $imageBasePath; ?>media-playback-pause.png" alt="Pause" /></div>
						
						<span id="previous-image-cell"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="prev-image-link" style="margin:0 10px;" onclick="javascript:changeImage(<?php echo $prevThumbPosition; ?>);return false;"><img src="<?php echo $imageBasePath; ?>media-seek-backward.png" alt="Previous Image" /></a></span>
						<a href="#null" id="slideshow-link" style="margin:0 10px;" slideshowStatus="Play"><script type="text/javascript">$(document).ready(function() { $("#slideshow-link").html($("#playHtml").html()); });</script></a>
						<span id="next-image-cell"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?>><a href="#null" id="next-image-link" style="margin:0 10px;" onclick="javascript:changeImage(<?php echo $currentImageId + 1; ?>);return false;"><img src="<?php echo $imageBasePath; ?>media-seek-forward.png" alt="Next Image" /></a></span>
						<a href="#null" id="photo-detail-link" style="margin:0 10px;"><img src="<?php echo $imageBasePath; ?>system-search.png" alt="Photo Details" /></a>
						<a href="<?php echo $selectedAlbumPath . '/' . $currentAlbumArray[$currentImageId]; ?>" id="view-original" style="margin:0 10px;" target="_blank"><img src="<?php echo $imageBasePath; ?>view-fullscreen.png" alt="View Original" /></a>
					
					
						<?php
						if ($numberOfSets > 1) {
							echo '<span id="page-links" style="font-size:small;">';
							for ($i=0;$i<$numberOfSets;$i++) {
								
								if ($i == round($currentImageId/16)) {
									$selectedStyle = ' style="color:#09679A;"';
								} else {
									$selectedStyle = "";
								}
								if ($i == ($numberOfSets-1)) {
									$lastStartNumber = $i+($i*15);
									echo '<a href="?selectedAlbum=', urlencode($selectedAlbum), '&amp;start=', $i+($i*15), '"', $selectedStyle, '>', ($i+($i*15))+1, '-', count($currentAlbumArray), '</a> ';
								} else {
									echo '<a href="?selectedAlbum=', urlencode($selectedAlbum), '&amp;start=', $i+($i*15), '"', $selectedStyle, '>', ($i+($i*15))+1, '-', ($i+($i*15))+16, '</a> ';
								}
							}
							echo '</span>';
						} else { $lastStartNumber = 0; }
						?>
					</div>
					
					<div id="current-image"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="overflow:hidden;">
						<?php if(confEnablePreview) { ?>
						<div id="preview-image" onmouseover="javascript:hidePreviewImage();"><img id="preview-image-img" src="<?php echo $selectedAlbumCachePath . '/' . ($mainImageSize/2) . "_" . md5($currentAlbumArray[0]); ?>" style="border: 4px solid #fff;<?php if ($mainImageSize <= "320") { echo " width: 160px;"; } ?>" alt="Preview Image" /></div>
						<?php } ?>
						<img id="current-image-img" src="<?php echo $selectedAlbumCachePath . '/' . $mainImageSize . "_" . md5($currentAlbumArray[$currentImageId]); ?>" style="margin:0 auto;border: 4px solid #fff;" alt="Current Image" /></div>
					<div id="current-image-original"<?php if(confEnablePreview) { ?> onmouseover="javascript:hidePreviewImage();"<?php } ?> style="position:absolute;top:-5000px;left:-5000px;overflow:hidden;"><img id="current-image-original-img" src="<?php echo $selectedAlbumPath . '/' .  $currentAlbumArray[$currentImageId]; ?>" style="visibility:hidden;" alt="Current Image" /></div>
				</td>

				<td valign="top" style="text-align:right;padding-top:21px;padding-right:1px; width:48px;">
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
<div style="clear:both;line-height:0;"></div>