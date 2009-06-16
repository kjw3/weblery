/*
//Author: Kevin Jones
//Email: kevin.d.jones@gmail.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 06/11/2009
//Copyright: Kevin Jones 2009
//License: ./documentation/License.txt
//
//Note: You should find no need to modify this file
//
//Configuration Parameters are set in configuration.php
//Please read over the documents in the documentation folder
*/
$(document).ready(function() {
	var $_GET = {};
	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
	    function decode(s) {
	        return decodeURIComponent(s).replace(/\+/g, " ");
	    }
	    $_GET[decode(arguments[1])] = decode(arguments[2]);
	});
	
	var $photoDetailDialog = $("#photo-detail").dialog({
		bgiframe: true,
		autoOpen: false,
		resizable: false,
		closeOnEscape: true,
		modal: true,
		width: "350",
		position: "top",
		title: "Photo Details",
		buttons: { "close": function() {$(this).dialog('close');} }
	});
	
	// Slideshow Functionality
	var timerId = '';
	function timer() {
		timerId = window.setTimeout(function() {
			tempOnClick = $("#next-image-link").attr("onClick").toString();
			if (tempOnClick.indexOf("start")) {
				$("#next-image-link").attr("play", "true");
			}
			$("#next-image-link").click();
			timer();
		}, 4000);
	}
	
	$("#slideshow-link").click( function(e) {
		e.preventDefault();
		if ( $("#slideshow-link").html() == "Pause"  ) {
			$("#slideshow-link").html("Play");
			clearTimeout(timerId);
		} else {
			$("#slideshow-link").html("Pause");
			timer();
		}
	});
		
	if ($_GET["play"] == 1) {
		$("#slideshow-link").html("Pause");
		timer();
	}
	
	// End Slideshow Functionality
	
	$("#photo-detail-link").click( function(e) {
		e.preventDefault();
		document.getElementById('photo-detail').innerHTML = 'Loading Photo Details...';
		if($photoDetailDialog) { $photoDetailDialog.dialog("open"); }
		$("#current-image-original-img").exifLoadSingle('current-image-original-img', function() {
			displayExif($("#current-image-original-img").exifPrettySingle('current-image-original-img'),$photoDetailDialog)
		});
	});
});


function displayExif(exifStrings, dialog) {
	var tempArray = new Array();
	var currentItem = new Array();
	var finalHtml = '<div style="float:left;width:50%;font-size:xx-small;font-weight:normal;text-align:left;padding-left:5px;">No photo details available</div>';
	var showAll = false;
	tempArray = exifStrings.join("").split("\r\n");
	//Loop through all Exif Tags
	if (tempArray.length > 1) {
		finalHtml = '';
		for(i=0;i<tempArray.length;i++) {
			currentItem = tempArray[i].split(" : ");
			showItem = false;
			switch(currentItem[0]) {
				case "Make" :
					showItem = true;
					break;
				case "Model" :
					showItem = true;
					break;
				case "DateTime" :
					showItem = true;
					break;
				case "ExposureTime" :
					currentItem[1] = getShutterSpeed(currentItem[1]);
					showItem = true;
					break;
				case "FNumber" :
					showItem = true;
					break;
				case "ExposureProgram" :
					showItem = true;
					break;
				case "ISOSpeedRatings" :
					showItem = true;
					break;
				case "MaxApertureValue" :
					showItem = true;
					break;
				case "MeteringMode" :
					showItem = true;
					break;
				case "Flash" :
					showItem = true;
					break;
				case "FocalLength" :
					showItem = true;
					break;
				case "WhiteBalance" :
					showItem = true;
					break;
				case "FocalLengthIn35mmFilm" :
					showItem = true;
					break;
				case "Contrast" :
					showItem = true;
					break;
				case "Saturation" :
					showItem = true;
					break;
				case "Sharpness" :
					showItem = true;
					break;
			}
			if (showItem || showAll) { 
				finalHtml += '<div style="float:left;width:45%;font-size:xx-small;font-weight:bold;text-align:right;">' +
					currentItem[0] + ':</div> <div style="float:left;width:50%;font-size:xx-small;font-weight:normal;text-align:left;padding-left:5px;">' +
					currentItem[1] + "</div><br>";
				}
		}
	}
	document.getElementById('photo-detail').innerHTML = finalHtml;
}

function getShutterSpeed(exifValue)
{
    var ShutterSpeedValues = new Array();
    var exifShutterSpeedValue;
   
    ShutterSpeedValues["1/4000"] = 1/4000;
    ShutterSpeedValues["1/3200"] = 1/3200;
    ShutterSpeedValues["1/2500"] = 1/2500;    
    ShutterSpeedValues["1/2000"] = 1/2000;
    ShutterSpeedValues["1/1600"] = 1/1600;
    ShutterSpeedValues["1/1500"] = 1/1500;
    ShutterSpeedValues["1/1250"] = 1/1250;
    ShutterSpeedValues["1/1000"] = 1/1000;
    ShutterSpeedValues["1/800"] = 1/800;
    ShutterSpeedValues["1/750"] = 1/750;
    ShutterSpeedValues["1/640"] = 1/640;
    ShutterSpeedValues["1/500"] = 1/500;
    ShutterSpeedValues["1/400"] = 1/400;
    ShutterSpeedValues["1/320"] = 1/320;
    ShutterSpeedValues["1/250"] = 1/250;
    ShutterSpeedValues["1/200"] = 1/200;
    ShutterSpeedValues["1/180"] = 1/180;
    ShutterSpeedValues["1/160"] = 1/160;
    ShutterSpeedValues["1/125"] = 1/125;
    ShutterSpeedValues["1/100"] = 1/100;
    ShutterSpeedValues["1/90"] = 1/90;
    ShutterSpeedValues["1/80"] = 1/80;
    ShutterSpeedValues["1/60"] = 1/60;
    ShutterSpeedValues["1/50"] = 1/50;
    ShutterSpeedValues["1/45"] = 1/45;
    ShutterSpeedValues["1/40"] = 1/40;    
    ShutterSpeedValues["1/30"] = 1/30;
    ShutterSpeedValues["1/25"] = 1/25;
    ShutterSpeedValues["1/20"] = 1/20;
    ShutterSpeedValues["1/15"] = 1/15;
    ShutterSpeedValues["1/13"] = 1/13;
    ShutterSpeedValues["1/10"] = 1/10;
    ShutterSpeedValues["1/8"] = 1/8;
    ShutterSpeedValues["1/6"] = 1/6;
    ShutterSpeedValues["1/5"] = 1/5;
    ShutterSpeedValues["1/4"] = 1/4;
    ShutterSpeedValues["1/3.2"] = 1/3.2;    
    ShutterSpeedValues["1/2.5"] = 1/2.5;
    ShutterSpeedValues["1/2"] = 1/2;
    ShutterSpeedValues["1.5"] = 1.5;
    ShutterSpeedValues["1/0.7"] = 1/0.7;
   
    for (value in ShutterSpeedValues)
    {
        if(exifValue == eval(ShutterSpeedValues[value]))
        {
            thisExifShutterSpeedValue = value;
            break;
        }
        else
        {
            thisExifShutterSpeedValue = 'n/a [' + exifValue + ']';
        }            
    }
   
    return thisExifShutterSpeedValue;
}

//Preloading Images for current photo set
jQuery.preloadImages = function(imageSize,thumbsToUse,origsToUse) {
	preloadArray = new Array();
	j=0;
	switch(imageSize) {
		case "320" :
			oppositeImageSize = "640";
			break;
		default:
			oppositeImageSize = "320";
			break;
	}
	
	for (i=0;i<thumbsToUse.length;i++) {
		preloadArray[j] = thumbsToUse[i].replace(mainImageSize + "_", oppositeImageSize + "_");
		preloadArray[j+1] = thumbsToUse[i].replace(mainImageSize + "_", "tn_");
		preloadArray[j+2] = origsToUse[i];
		preloadArray[j+3] = thumbsToUse[i];
		j += 4;
	}
  
	imageObj = new Image();
	imageObj.exif = true;
	for(var i = 0; i < preloadArray.length; i++) {
		imageObj.src=preloadArray[i];
	}
}
// End Preloading Images
