<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Includes all PorPOISe source files
 *
 * @package PorPOISe
 */

/** Geographic utilities */
require_once("geoutil.class.php");
/** POI classes */
require_once("poi.class.php");
/** POI collector interface */
require_once("poicollector.interface.php");
/** POI collector for flat files */
require_once("flatpoicollector.class.php");
/** POI collector for XML files */
require_once("xmlpoicollector.class.php");
/** POI collector for SQL databases */
require_once("sqlpoicollector.class.php");
/** Layer definition */
require_once("layer.class.php");
/** server and server factory */
require_once("poiserver.class.php");
/** configuration */
require_once("porpoiseconfig.class.php");
