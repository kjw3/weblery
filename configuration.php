<?php
/************** Configuration Parameters ***********************/

//The relative url path to your weblery.php file
//If the weblery.php file is sitting in your www root folder leave this variable as is
//If not here is an example:
//ex: http://www.mydomain.com/weblery/ set confWebleryBasePath to 'weblery/'
DEFINE('confWebleryBasePath','');

//The url path to your album directory relative to your weblery.php file
DEFINE('confGalleryBasePath','albums');

//Set the file name where the weblery class is included
DEFINE('confBaseStartPage','demo.php');

//Set the layout file to be used for the look and feel
DEFINE('confLayoutFile','layout3.php');

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
DEFINE('confEnablePreview',false);

//Enable preloading of all images for the current page
DEFINE('confEnablePreloadImages',true);

//If true, photos will be sorted in reverse order by file name
DEFINE('confReverseSort',false);

/*************** End Configuration Parameters ******************/
?>