<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Includes all PorPOISe source files and does some global
 * definitions
 *
 * @package PorPOISe
 */

/** All other config is in config.xml */
define("PORPOISE_CONFIG_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR . "config");

/** Geographic utilities */
require_once("geoutil.class.php");
/** Miscellaneous functions */
require_once("functions.php");

/** POI classes */
require_once("poi.class.php");
/** Response class */
require_once("layarresponse.class.php");
/** POIConnector interface */
require_once("poiconnector.interface.php");
/** POI connector abstract class */
require_once("poiconnector.class.php");

/** Base POIConnectors */
require_once("sqlpoiconnector.class.php");
require_once("xmlpoiconnector.class.php");
require_once("flatpoiconnector.class.php");

/** Layer definition */
require_once("layer.class.php");
/** Filter class for filter values */
require_once("filter.class.php");
/** server and server factory */
require_once("poiserver.class.php");
/** configuration */
require_once("porpoiseconfig.class.php");
/** web-app abstract class */
require_once("web-app.class.php");
/** OAuth aware HTTP client */
require_once("httprequest.class.php");
/** User persistence classes */
require_once("user.class.php");



/* change to config directory for remainder of execution */
chdir(PORPOISE_CONFIG_PATH);
