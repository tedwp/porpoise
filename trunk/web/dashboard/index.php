<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * PorPOISe Dashboard
 *
 * @package PorPOISe
 * @subpackage Dashboard
 */

/** Dashboard includes */
require_once("dashboard.inc.php");

/* basic request validation */
if (empty($_REQUEST["action"])) {
	$_action = "main";
} else {
	$_action = $_REQUEST["action"];
}

/* handle action */
try {
	switch($_action) {
	case "main":
		GUI::printMessage("%s", GUI::createMainScreen());
		break;
	case "viewLayer":
		GUI::printMessage("%s", GUI::createLayerScreen($_REQUEST["layerName"]));
		break;
	default:
		throw new Exception(sprintf("Invalid action: %s", $_action));
	}
} catch (Exception $e) {
	GUI::printError("%s", $e->getMessage());
	GUI::printMessage("%s", GUI::createMainScreen());
}	
exit();

$pois = DML::getPOIs("example");
printf("<table>\n");
foreach ($pois as $poi) {
	printf("<tr><td>%s</td><td>%s,%s</td></tr>\n", $poi->title, $poi->lat, $poi->lon);
}
printf("</table>");
