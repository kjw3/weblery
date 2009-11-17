<?php
/************** Configuration Parameters ***********************/

//The path to your weblery.php file
DEFINE('confWebleryBasePath','');

//The path to your album directory relative to your weblery.php file
DEFINE('confGalleryBasePath','albums');

//Set the file name where the weblery class is included
DEFINE('confBaseStartPage','demo.php');

//Set the layout file to be used for the look and feel
DEFINE('confLayoutFile','layout1.php');

//Width of main image in pixels
DEFINE('confMainImageSize','640');

//Set the image quality for the resized iamges and thumbs
// 1 = lower quality faster speed
// 5 = higher quality slower speed
DEFINE('confImageQuality',3);

//Set the default Width and Height of your thumbnails
DEFINE('confDefaultThumbWidth','48');
DEFINE('confDefaultThumbHeight','48');

//Enable the rollover photo preview on the thumbnails
//true turns the preview on, false turns it off
DEFINE('confEnablePreview',true);

//Enable the rollover photo preview on the thumbnails
DEFINE('confEnablePreloadImages',false);

//If true, photos will be sorted in reverse order by file name
DEFINE('confReverseSort',false);

/*************** End Configuration Parameters ******************/
?>