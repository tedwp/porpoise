<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * PorPOISe dashboard GUI
 *
 * @package PorPOISe
 * @subpackage Dashboard
 */

// use output buffering so we can prevent output of we want to
ob_start(array("GUI", "finalize"));

/**
 * GUI class
 *
 * All methods are static
 *
 * @package PorPOISe
 * @subpackage Dashboard
 */
class GUI {
	/**
	 * Callback for ob_start()
	 *
	 * Adds header and footer to HTML output and does post-processing
	 * if required
	 *
	 * @param string $output The output in the buffer
	 * @param int $state A bitfield specifying what state the script is in (start, cont, end)
	 *
	 * @return string The new output
	 */
	public static function finalize($output, $state) {
		$result = "";
		if ($state & PHP_OUTPUT_HANDLER_START) {
			$result .= self::createHeader();
		}
		$result .= $output;
		if ($state & PHP_OUTPUT_HANDLER_END) {
			$result .= self::createFooter();
		}
		return $result;
	}

	/**
	 * Print a formatted message
	 *
	 * @param string $message sprintf-formatted message
	 * 
	 * @return void
	 */
	public static function printMessage($message) {
		$args = func_get_args();
		/* remove first argument, which is $message */
		array_splice($args, 0, 1);
		vprintf($message, $args);
	}

	/**
	 * Print an error message
	 *
	 * @param string $message sprintf-formatted message
	 *
	 * @return void
	 */
	public static function printError($message) {
		$args = func_get_args();
		$args[0] = sprintf("<p class=\"error\">%s</p>\n", $args[0]);
		call_user_func_array(array("GUI", "printMessage"), $args);
	}

	/**
	 * Create a header
	 *
	 * @return string
	 */
	public static function createHeader() {
		return
"<html>
<head>
<title>PorPOISe POI Management Interface</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\">
</head>
<body>";
	}

	/**
	 * Create a footer
	 *
	 * @return string
	 */
	public static function createFooter() {
		return "</body>\n</html>";
	}

	/**
	 * Create "main" screen
	 *
	 * @return string
	 */
	public static function createMainScreen() {
		$result = "";
		$result .= "<p>Welcome to PorPOISe</p>\n";
		$result .= self::createMainConfigurationTable();
		$result .= self::createLayerList();
		return $result;
	}

	/**
	 * Create a table displaying current configuration
	 *
	 * @return string
	 */
	public static function createMainConfigurationTable() {
		$config = DML::getConfiguration();
		$result = "";
		$result .= "<table>\n";
		$result .= sprintf("<tr><td>Developer ID</td><td>%s</td></tr>\n", $config->developerID);
		$result .= sprintf("<tr><td>Developer key</td><td>%s</td></tr>\n", $config->developerKey);
		$result .= sprintf("</table>\n");
		return $result;
	}

	/**
	 * Create a list of layers
	 *
	 * @return string
	 */
	public static function createLayerList() {
		$config = DML::getConfiguration();
		$result = "";
		$result .= "<ul>\n";
		foreach ($config->layerDefinitions as $layerDefinition) {
			$result .= sprintf("<li><a href=\"%s?action=viewLayer&layerName=%s\">%s</a></li>\n", $_SERVER["PHP_SELF"], $layerDefinition->name, $layerDefinition->name);
		}
		$result .= "</ul>\n";
		return $result;
	}

	/**
	 * Create a screen for viewing/editing a layer
	 *
	 * @param string $layerName
	 *
	 * @return string
	 */
	public static function createLayerScreen($layerName) {
		$result = "";
		$result .= sprintf("<p>Layer name: %s</p>\n", $layerName);
		$result .= self::createPOITable($layerName);
		return $result;
	}

	/**
	 * Create a list of POIs for a layer
	 *
	 * @param string $layerName
	 *
	 * @return string
	 */
	public static function createPOITable($layerName) {
		$result = "";
		$pois = DML::getPOIs($layerName);
		if ($pois == NULL || $pois === FALSE) {
			throw new Exception("Error retrieving POIs");
		}
		$result .= "<table>\n";
		$result .= "<tr><th>Title</th><th>Lat/lon</th></tr>\n";
		foreach ($pois as $poi) {
			$result .= "<tr>\n";
			$result .= sprintf("<td><a href=\"%s?action=viewPOI\">%s</a></td>\n", $_SERVER["PHP_SELF"], $poi->title);
			$result .= sprintf("<td>%s,%s</td>\n", $poi->lat, $poi->lon);
			$result .= "</tr>\n";
		}
		$result .= "</table>\n";
		return $result;
	}
}
