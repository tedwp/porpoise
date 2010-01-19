<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Layer for Layar
 *
 * @package PorPOISe
 */

/**
 * Requires POI
 */
require_once("poi.class.php");

/**
 * Layer description class
 *
 * @package PorPOISe
 */
class Layer {
	// number of POIs returned per page
	const POIS_PER_PAGE = 10;

	// verify hash or not?
	const VERIFY_HASH = TRUE;

	// fields making up this layer
	public $developerId;
	public $developerKey;
	public $layerName;
	
	/** @var POICollector */
	protected $poiCollector;

	// state variables for returning POI information
	protected $nearbyPOIs;
	protected $hasMorePOIs;
	protected $nextPageKey;

	/**
	 * Constructor
	 *
	 * @param string $layerName
	 * @param string $developerId
	 * @param string $developerKey
	 */
	public function __construct($layerName, $developerId, $developerKey) {
		$this->layerName = $layerName;
		$this->developerId = $developerId;
		$this->developerKey = $developerKey;
	}

	/**
	 * Set a POI collector
	 *
	 * @param POICollector $poiCollector
	 */
	public function setPOICollector(POICollector $poiCollector) {
		$this->poiCollector = $poiCollector;
	}

	/**
	 * Add a POI to this layer
	 *
	 * @return void
	 */
	public function addPOI(POI $poi) {
		$this->pois[] = $poi;
	}

	/**
	 * Determines nearby POIs and stores them for later use
	 *
	 * @param float $lat Latitude of user's position
	 * @param float $lon Longitude of user's position
	 * @param int $radius Radius of user's view in meters
	 * @param int $accuracy Accuracy of user's GPS location in meters
	 * @param array $options Extra options (RADIOBOX, SEARCHBOX, CUSTOM_SLIDER, pageKey)
	 *
	 * @return void
	 */
	public function determineNearbyPOIs($lat, $lon, $radius, $accuracy, $options) {
		$this->nearbyPOIs = array();
		if (!empty($this->poiCollector)) {
			$this->nearbyPOIs = $this->poiCollector->getPOIs($lat, $lon, $radius, $accuracy, $options);
		}
		$this->hasMorePOIs = FALSE;
		$this->nextPageKey = NULL;

		if (isset($options["pageKey"])) {
			$offset = $options["pageKey"] * self::POIS_PER_PAGE;
			if (count($this->nearbyPOIs) - $offset > self::POIS_PER_PAGE) {
				$this->hasMorePOIs = TRUE;
				$this->nextPageKey = $options["pageKey"] + 1;
			}
		} else {
			$offset = 0;
		}
		if ($offset > count($this->nearbyPOIs)) {
			// no POIs on this page
			$nearbyPOIs = array();
		} else {
			$this->nearbyPOIs = array_slice($this->nearbyPOIs, $offset, self::POIS_PER_PAGE);
		}
	}

	/**
	 * Get the nearby POIs determined after calling determineNearbyPOIs()
	 *
	 * @return POI[]
	 */
	public function getNearbyPOIs() {
		return $this->nearbyPOIs;
	}

	/**
	 * Check if there are more POIs than returned (for additional pages)
	 *
	 * @return bool
	 */
	public function hasMorePOIs() {
		return $this->hasMorePOIs;
	}

	/**
	 * Get the key of the next page (if there are more POIs)
	 *
	 * @return bool
	 */
	public function getNextPageKey() {
		return $this->nextPageKey;
	}

	/**
	 * Verify a supplied hash
	 *
	 * Check can be disabled by setting Layer::VERIFY_HASH to FALSE
	 *
	 * @return bool
	 */
	public function isValidHash($hash, $timestamp) {
		if (!self::VERIFY_HASH) {
			return TRUE;
		}
		$goodHash = sha1($this->developerKey . $timestamp);
		return $hash == $goodHash;
	}
}

