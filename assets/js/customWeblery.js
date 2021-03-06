/*
//Author: Kevin Jones
//Email: kevin@weblery.com
//Web Address: http://www.weblery.com/
//Date Last Modified: 11/04/2011
//Copyright: KJW3, LLC 2011
//License: weblery/License.txt
//Manual: weblery/manual.pdf
//
//Note: You should find no need to modify this file
*/
$(document).ready(function() {
	
	var $photoDetailDialog = $("#photo-detail").dialog({
		bgiframe: true,
		autoOpen: false,
		resizable: false,
		closeOnEscape: true,
		modal: true,
		width: "350",
		draggable: true,
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

		if ( $("#slideshow-link").attr("slideshowStatus") == "Pause"  ) {
			$("#slideshow-link").attr("slideshowStatus", "Play");
			$("#slideshow-link").html($("#playHtml").html());
			$("#next-image-link").attr("play", "false");
			clearTimeout(timerId);
			//This is a hack to clear all existing timeouts
			var highestTimeoutId = setTimeout("");
			for (var i = 0 ; i < highestTimeoutId ; i++) clearTimeout(i);
		} else {
			$("#slideshow-link").attr("slideshowStatus", "Pause");
			$("#slideshow-link").html($("#pauseHtml").html());
			timer();
		}

	});

	// End Slideshow Functionality
	
	$("#photo-detail-link").click( function(e) {
		e.preventDefault();
		
		$('#photo-detail').html('Loading Photo Details...');

		$("#current-image-original-img").exifLoadSingle('current-image-original-img', function() {
			displayExif($("#current-image-original-img").exifPrettySingle('current-image-original-img'));
		});
		
		if($photoDetailDialog) $photoDetailDialog.dialog("open");
	});
	
	$(".setLink").click(function(e){
		e.preventDefault();
		$.ajax({
		  url: $(this).attr('weblery'),
		  data: {
		  	selectedAlbum:$(this).attr('selectedAlbum'),
		  	naked:$(this).attr('naked'),
		  	start:$(this).attr('start')
		  },
		  success: function(data){
		    $(".ui-dialog,#photo-detail").remove();
		    $("#weblery-content").html(data);
		  }
		});
	});
});


function displayExif(exifStrings) {
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

	$('#photo-detail').html(finalHtml);

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
jQuery.preloadImages = function(imageSize,thumbsToUse,origsToUse,enablePreview,start) {
	preloadArray = new Array();
	j=0;
	switch(imageSize) {
		case (imageSize/2) :
			oppositeImageSize = imageSize;
			break;
		default:
			oppositeImageSize = (imageSize/2);
			break;
	}
	
	//Only load the images that will be displayed on this page.
	finish = thumbsToUse.length;
	if ((thumbsToUse.length - start) > 16) finish = start + 16;
	
	for (i=start;i<finish;i++) {
		preloadArray[j] = thumbsToUse[i];
		preloadArray[j+1] = thumbsToUse[i].replace(mainImageSize + "_", "tn_");
		preloadArray[j+2] = origsToUse[i];
		if (enablePreview) preloadArray[j+3] = thumbsToUse[i].replace(mainImageSize + "_", oppositeImageSize + "_");
		if (enablePreview) {
			j += 4;
		} else {
			j += 3;
		}
	}
  
	for(var i = 0; i < preloadArray.length; i++) {
		jQuery("<img>").attr("src", preloadArray[i]);
	}
}
// End Preloading Images
