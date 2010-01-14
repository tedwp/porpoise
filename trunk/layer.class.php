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
	
	/** @var POIConnector */
	protected $poiConnector;

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
	 * Set a POI connector
	 *
	 * @param POIConnector $poiConnector
	 */
	public function setPOIConnector(POIConnector $poiConnector) {
		$this->poiConnector = $poiConnector;
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
	 * Filter $filter
	 *
	 * @return void
	 */
	public function determineNearbyPOIs(Filter $filter) {
		$this->nearbyPOIs = array();
		if (!empty($this->poiConnector)) {
			$this->nearbyPOIs = $this->poiConnector->getPOIs($filter);
		}
		$this->hasMorePOIs = FALSE;
		$this->nextPageKey = NULL;

		if (isset($filter->pageKey)) {
			$offset = $filter->pageKey * self::POIS_PER_PAGE;
		} else {
			$offset = 0;
		}
		if (count($this->nearbyPOIs) - $offset > self::POIS_PER_PAGE) {
			$this->hasMorePOIs = TRUE;
			$this->nextPageKey = ($offset / self::POIS_PER_PAGE) + 1;
		}
		if ($offset > count($this->nearbyPOIs)) {
			// no POIs on this page
			$nearbyPOIs = array();
		} else {
			$limit = min(self::POIS_PER_PAGE, count($this->nearbyPOIs) - $offset);
			$this->nearbyPOIs = array_slice($this->nearbyPOIs, $offset, $limit);
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

