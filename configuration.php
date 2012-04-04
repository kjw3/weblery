<?php
/************** Configuration Parameters ***********************/

//The url path to your album directory relative to your weblery.php file
DEFINE('confGalleryBasePath','albums');

//Set the file name where the weblery class is included
DEFINE('confBaseStartPage','demo.php');

//Set the layout file to be used for the look and feel
DEFINE('confLayoutFile','layout3.php');

//Width of main image in pixels
DEFINE('confMainImageSize','640');

//Set the default Width and Height of your thumbnails
//Note: If you change this after you have initialized albums
//You will need to delete the associated album folder from the
//album_cache directory. Then you will need to reinitialize each album
DEFINE('confDefaultThumbWidth','48');

//Set the image quality for the resized images and thumbs
// 1 = lower quality faster speed
// 5 = higher quality slower speed
DEFINE('confImageQuality',3);

//Enable the rollover photo preview on the thumbnails
//true turns the preview on, false turns it off
DEFINE('confEnablePreview',false);

//Enable preloading of all images for the current page
DEFINE('confEnablePreloadImages',true);

//If true, photos will be sorted in reverse order by file name
DEFINE('confReverseSort',true);

//Enable the Powered by Weblery Link
//True shows the link, False to remove the link
DEFINE('confEnableWebleryLink',true);

/*************** End Configuration Parameters ******************/
?>