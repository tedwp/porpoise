<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * PorPOISe entry point
 *
 * @package PorPOISe
 */

/* 
 * Change working directory to where the rest of PorPOISe resides.
 *
 * For security reasons it is advisable to make only this file
 * accessible from the web. Other files may contain sensitive
 * information (especially config.xml), so you'll want to keep
 * them away from spying eyes!
 */
chdir("..");

/**
 * Include PorPOISe
 */
require_once("poiserver.class.php");

/* start of server*/

/* use most strict warnings, enforces neat and correct coding */
error_reporting(E_ALL | E_STRICT);

/* open config file */
try {
	$config = new SimpleXMLElement("config.xml", 0, TRUE);
	$developerID = (string)$config->{"developer-id"};
	$developerKey = (string)$config->{"developer-key"};
} catch (Exception $e) {
	printf("Error loading configuration: %s", $e->getMessage());
}

/* Set the proper content type */
header("Content-Type: text/javascript");

/* initialize server factory */
$factory = new LayarPOIServerFactory($developerID, $developerKey);

/* create server */
$server = $factory->createLayarPOIServerFromSimpleXMLConfig($config->layers);

/* handle the request, and that's the end of it */
$server->handleRequest();
