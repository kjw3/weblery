<?php
session_start();

//No Need to access this page directly
if (!isset($currentStep) || (isset($currentStep) && ($currentStep < 0 || $currentStep > 3))) {
	header('Location: ../weblery.php');
	exit;
}

if (isset($_SESSION['initStepCount']) && $_SESSION['initStepCount'] > 5) {
	echo "Too many page reloads.  Please click the link below to start over<br />";
	echo '<a href="./src/restart.php">Start Over</a>';
	exit;
}
?>
<style>
body {
margin:10px;
}
</style>
<h1>Album Initialization</h1>
<?php
$nextStep = $currentStep + 1;

//Check to make sure steps are not rerun over and over
if ((isset($_SESSION['initAlbum']) && $_SESSION['initAlbum'] == self::__get('selectedAlbumPath')) &&
		(isset($_SESSION['initStep']) && $_SESSION['initStep'] == $currentStep)) {
	$currentStep += 1;
}

switch($currentStep) {
	case 1 :
		self::regenThumbs('320');
		echo '<h2>Step', $currentStep, " of ", $numberOfSteps, ' Complete.</h2>';
		$continueLinkText = "Generate 640 width images";
		break;
	case 2 :
		self::regenThumbs('640');
		echo '<h2>Step ', $currentStep, " of ", $numberOfSteps, ' Complete.</h2>';
		$continueLinkText = "Generate thumbnail images";
		break;
	case 3 :
		self::regenThumbs('');
		$fileHandle = fopen(self::__get('selectedAlbumPath').'initialized', 'w');
		fwrite($fileHandle, 'album initialized');
		fclose($fileHandle);
		unset($_SESSION['initAlbum']);
		unset($_SESSION['initStep']);
		unset($_SESSION['initStepCount']);
		echo '<h2>Step ', $currentStep, " of ", $numberOfSteps, ' Complete.</h2>';
		$continueLinkText = "View your new album";
		break;
	default :
		echo '<p>This album first needs to be initialized.</p>';
		$continueLinkText = "Generate 320 width images";
		break;
}
?>

<div id="progressbar" style="display:none;"><img id="loading-img" src="./src/img/loading.gif" alt="Generating Images... (loading animation)" /></div>
<p id="redirect-text">This page will continue automatically in 5 seconds.</p>
<p id="explanation-text">If for some reason it does not continue, click the step link below.</p>

<?php
if ($currentStep <= 2) {
	echo '<p id="continue-link-paragraph">Step ', $nextStep, ': <a href="#null" id="continue-init-link" onClick="setPageLoading(\'', self::__get('selectedAlbum'), '\',\'', $nextStep, '\');">', $continueLinkText, '</a></p>';
} else {
	echo '<p id="continue-link-paragraph"><a href="#null" id="view-album-link" onClick="window.location=\'', self::__get('baseStartPage'), '?selectedAlbum=', self::__get('selectedAlbum'), '\';">', $continueLinkText, '</a></p>';
}

if ($currentStep == 0 || $currentStep == 1 || $currentStep == 2) {
	$_SESSION['initAlbum'] = self::__get('selectedAlbumPath');
	$_SESSION['initStep'] = $currentStep;
	if (!isset($_SESSION['initStepCount'])) { $_SESSION['initStepCount'] = 0; }
	$_SESSION['initStepCount'] += 1;
}
?>
<script type="text/javascript">
<?php if ($currentStep < 3) { ?>
	function setPageLoading(thisAlbum, thisStep) {
		$('#progressbar').css('display','block');
		$("#explanation-text").html("Please do not use your browser's back or refresh buttons at this time.");
		$("#redirect-text").html('');
		$("#continue-link-paragraph").html("");
		jQuery.get("weblery.php", { selectedAlbum : thisAlbum, step : thisStep },  function(data) { $("#weblery-content").html(data); });
	}

	window.setTimeout(function() { 
		$("#continue-init-link").click();
	}, 5000);
<?php } else { ?>
	window.setTimeout(function() { 
		$("#view-album-link").click();
	}, 5000);
<?php } ?>
</script>
