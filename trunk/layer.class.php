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
 * Requires LayarResponse
 */
require_once("layarresponse.class.php");

/**
 * Layer description class
 *
 * @package PorPOISe
 */
class Layer {
	/** @var int number of POIs returned per page */
	const POIS_PER_PAGE = 10;

	/** @var bool verify hash or not? */
	/** @todo add timeout validation to hash validation */
	const VERIFY_HASH = TRUE;

	/** @var string developer ID */
	public $developerId;
	/** @var string developer key for hash verification */
	public $developerKey;
	/** @var string layer name */
	public $layerName;
	
	/** @var POIConnector */
	protected $poiConnector;

	/** @var LayarResponse The response we're serving */
	protected $response;
	/** @var bool Whether or not there are more POIs to be retrieved from the last request */
	protected $hasMorePOIs;
	/** @var string key for the next page of POIs */
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
	 * Determines nearby POIs and stores them for later use
	 *
	 * @param Filter $filter
	 *
	 * @return int number of POIs
	 */
	public function determineNearbyPOIs(Filter $filter) {
		if (isset($filter->pageKey)) {
			$offset = $filter->pageKey * self::POIS_PER_PAGE;
		} else {
			$offset = 0;
		}

		if (($offset == 0 || // always reload for 1st page request
			!$this->session_restore($filter->userID)) && // or when no session data exists
				!empty($this->poiConnector)) {

					$this->response = $this->poiConnector->getLayarResponse($filter);
					$pois = $this->response->hotspots;
					
					foreach($pois as $poi) {
						if ($poi->distance > $this->response->radius) {
							$this->response->radius = $poi->distance;
						}
					}
					$this->session_save($filter->userID);
		}
		// iterate over POIs and determine max distance
		// TODO: do something sensible with this
		// current implementation adds all POIs in the order they are
		// retrieved, while according to the spec max 50 POIs are displayed.
		// So limit POIs to max. 50, optionally after sorting by distance.
		// Maybe make the sorting order a config setting
		//
		// JdS 2010-07-08
		// --- 8< ---
		// ordering is done by POIConnectors using the most
		// efficient technique available for the specific data source
		//
		// Propose to let POI cutoff be determined by client, not enforce
		// 50 POI maximum in server
		// --- >8 ---
		//
		// JdS 2010-07-08
		// --- 8< ---
		// TODO: rewrite the last part of this method. We're cutting in the
		// object's response->nearbyPOIs for the final response. This works
		// because the complete set has already been saved in the session a
		// few lines before, but this approach is a bit murky. However, other
		// parts of PorPOISe rely on getNearbyPOIs to return only the POIs
		// for the current page so if we're gonna separate the POI sets for
		// the current page and the overall request we need to fix some more
		// lines than just the next 10 or so
		// --- >8 ---
				
		$this->hasMorePOIs = FALSE;
		$this->nextPageKey = NULL;
		$numPois = count($this->response->nearbyPOIs);
		
		if ($numPois - $offset > self::POIS_PER_PAGE) {
			$this->hasMorePOIs = TRUE;
			$this->nextPageKey = ($offset / self::POIS_PER_PAGE) + 1;
		}
		if ($offset > $numPois) {
			// no POIs on this page
			$this->response->nearbyPOIs = array();
		} else {
			$limit = min(self::POIS_PER_PAGE, $numPois - $offset);
			$this->response->nearbyPOIs = array_slice($this->response->nearbyPOIs, $offset, $limit);
		}
		if (!$this->hasMorePOIs) {
			$this->session_delete($filter->userID);
		}
		return $numPois;
	}

	/**
	 * Initialize session data
	 *
	 * @param string $sid
	 *
	 * @return void
	 */
	// NOTE: session ID needs to be set correctly, see also WebApp class
	protected function session_init($sid) {
		if ($sid != session_id($sid)) {
			@session_destroy(); // ugly suppression of warnings if no session exists
			session_id($sid);
			session_name('PorPOISe');
			session_start();
		}
	}

	/**
	 * Restore session data for an ongoing request
	 *
	 * Tries to load response data and already found POIs for an
	 * ongoing request, i.e. the client is coming back with the
	 * nextPageKey for more POIs. We cached those POIs in the
	 * session, this method loads the data
	 *
	 * @param string $sid The session ID
	 *
	 * @return bool TRUE on success, FALSE if the session or the request is corrupt
	 */
	protected function session_restore($sid) {
		$this->session_init($sid);
		// sanity check: are we requesting POIs from the same layer?
		if (@$_SESSION['layerName'] != $this->layerName) {
			$this->session_delete($sid);
			return false;
		}
		if (isset($_SESSION['response'])) {
			$this->response = $_SESSION['response'];
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Save data for an ongoing request in the session
	 *
	 * @param string $sid The session ID
	 *
	 * @return void
	 */
	protected function session_save($sid) {
		$this->session_init($sid);
		$_SESSION["response"] = $this->response;
		$_SESSION['layerName'] = $this->layerName;
		session_commit();
	}
	
	/**
	 * Delete saved session data
	 *
	 * @param string $sid The session ID
	 *
	 * @return void
	 */
	protected function session_delete($sid) {
		$this->session_init($sid);
		unset($_SESSION['response']);
		unset($_SESSION['layerName']);
		session_commit();
	}
	
	
	/**
	 * Get the nearby POIs determined after calling determineNearbyPOIs()
	 *
	 * @return POI[]
	 */
	public function getNearbyPOIs() {
		return $this->response->nearbyPOIs;
	}

		
	/**
	 * Get the Layer name
	 *
	 * @return string
	 */
	public function getLayerName() {
		return $this->layerName;
	}
		
	/**
	 * Get the max. radius plus some margin
	 *
	 * @return int
	 */
	public function getRadius() {
		return $this->response->radius;
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
	 * @return string
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

