<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Miscellaneous functions
 *
 * @package PorPOISe
 */

/**
 * Sort an array of objects by field name. The sort algorithm is a
 * stable merge sort.
 *
 * @param string field The field to sort on
 * @param array $ar The array to be sorted
 * @param bool descending Sort descending (default FALSE)
 
 * @return array
 */ 
function objectSort($field, array $ar, $descending = FALSE) {
	// check stop condition
	if (count($ar) < 2) {
		return $ar;
	}
	// split in equal halves and sort both
	$split = floor(count($ar) / 2);
	$ar1 = objectSort($field, array_slice($ar, 0, $split), $descending);
	$ar2 = objectSort($field, array_slice($ar, $split), $descending);
	// now merge
	$result = array();
	$i = $j = 0;
	while ($i < count($ar1) && $j < count($ar2)) {
		if ($descending) {
			if ($ar2[$j]->$field >= $ar1[$i]->$field) {
				$result[] = $ar2[$j];
				$j++;
			} else {
				$result[] = $ar1[$i];
				$i++;
			}
		} else {
			if ($ar1[$i]->$field <= $ar2[$j]->$field) {
				$result[] = $ar1[$i];
				$i++;
			} else {
				$result[] = $ar2[$j];
				$j++;
			}
		}
	}
	// add trailing elements
	for (; $i < count($ar1); $i++) {
		$result[] = $ar1[$i];
	}
	for (; $j < count($ar2); $j++) {
		$result[] = $ar2[$j];
	}
	return $result;
}

/**
 * Undo magic quotes if they have been enabled
 *
 * @return void
 */
function undo_magic_quotes_gpc() {
	if (!function_exists("get_magic_quotes_gpc")) {
		// magic_quotes_gpc is deprecated in PHP 5.3, this function may disappear in the future
		// so check for its existence
		return;
	}
	if (get_magic_quotes_gpc()) {
		/*foreach ($_REQUEST as $key => $value) $_REQUEST[$key] = stripslashes($value);
		foreach ($_GET as $key => $value) $_GET[$key] = stripslashes($value);
		foreach ($_POST as $key => $value) $_POST[$key] = stripslashes($value);
		foreach ($_COOKIE as $key => $value) $_COOKIE[$key] = stripslashes($value);*/
		stripslashes_array($_REQUEST);
		stripslashes_array($_GET);
		stripslashes_array($_POST);
		stripslashes_array($_COOKIE);
	}
}

/**
 * Strip slashes from elements in an array (recursively if necessary)
 *
 * Alters the argument
 *
 * @param &$ar
 * @return void
 */
function stripslashes_array(&$ar) {
	foreach ($ar as $key => $value) {
		if (is_array($value)) {
			stripslashes_array($ar[$key]);
		} else {
			$ar[$key] = stripslashes($value);
		}
	}
}
