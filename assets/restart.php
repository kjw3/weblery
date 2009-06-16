<?php
session_start();

//No Need to access this page directly
if (!isset($_SESSION['initAlbum'])) {
	header('Location: ../');
	exit;
}

unset($_SESSION['initAlbum']);
unset($_SESSION['initStep']);
unset($_SESSION['initStepCount']);

header('Location: ../');
?>