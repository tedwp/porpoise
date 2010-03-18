<?php

/*
 * PorPOISe
 * Copyright 2010 Squio
 * Released under a permissive license (see LICENSE)
 *
 */

/**
 * POI connector from Web API interface (e.g. REST API)
 * Extends WebApp class to use oAuth aware connections
 *
 * @package PorPOISe
 */

/**
 * Requires POI classes
 */
require_once("poi.class.php");

/**
 * Requires iPOIConnector interface
 */
require_once("poiconnector.interface.php");

/**
 * Requires GeoUtils
 */
require_once("geoutil.class.php");


/**
 * Requires WebApp class
 */
require_once("web-app.class.php");

/**
 * POI connector from http API services
 *
 * @package PorPOISe
 */
abstract class WebApiPOIConnector extends WebApp implements iPOIConnector {

	// subclass should implement method getPOIs()

	/**
	 * Override construtor for parent class WebApp
	 * to be compatible with interface iPOIConnector
	 */
	public function __construct($source) {
		// dummy
	}

	/**
	 * Initialize WebApp with Layerdefinition object
	 * 
	 * @param Laerdefinition $definition
	 *
	 * Initializes HTTP (optionally configured for oAuth requests)
	 */
	public function initDefinition($definition) {
		parent::__construct($definition);
	}

	/**
	 * INitialize user- and OAuth objects
	 * to be called just in time for any API request
	 * 
	 * @see getPOIs()
	 */
	public function init() {
		// try initialization of oAuth user token
		try {
			$this->http = $this->httpInit($this->definition->oauth);		
			$this->session_start();
			$this->userInit($this->definition);
			$this->initToken();
		} catch (Exception $e) {
			// fail silently
		}
	}

	public function storePOIs(array $pois, $mode = "update") {
		// N.A.
	}

	public function deletePOI($poiID) {
		// N.A.
	}

	public function setOption($name, $value) {
		// N.A.
	}


}
