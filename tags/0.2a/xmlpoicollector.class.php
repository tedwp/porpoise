<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * POI collector from XML files
 *
 * @package PorPOISe
 */

/**
 * Requires POI class
 */
require_once("poi.class.php");

/**
 * Requires POICollector interface
 */
require_once("poicollector.interface.php");
/**
 * Requires GeoUtil
 */
require_once("geoutil.class.php");

/**
 * POI collector from XML files
 *
 * @package PorPOISe
 */
class XMLPOICollector implements POICollector {
	/** @var string */
	protected $source;

	/**
	 * Constructor
	 *
	 * The field separator can be configured by modifying the public
	 * member $separator.
	 *
	 * @param string $source Filename of the POI file
	 */
	public function __construct($source) {
		$this->source = $source;
	}

	/**
	 * Get POIs
	 *
	 * @param float $lat
	 * @param float $lon
	 * @param int $radius
	 * @param int $accuracy
	 * @param array $options
	 *
	 * @return POI[]
	 *
	 * @throws Exception
	 */
	public function getPOIs($lat, $lon, $radius, $accuracy, $options) {
		$libxmlErrorHandlingState = libxml_use_internal_errors(TRUE);

		$xml = new SimpleXMLElement($this->source, 0, TRUE);
		if (empty($xml)) {
			throw new Exception("Failed to load data file");
		}

		$result = array();

		foreach ($xml->poi as $poiData) {
			$poi = new POI();
			foreach ($poiData->children() as $child) {
				$nodeName = $child->getName();
				if ($nodeName == "action") {
					$action = new POIAction();
					$action->uri = (string)$child->uri;
					$action->label = (string)$child->label;
					$poi->actions[] = $action;
				} else {
					$poi->$nodeName = (string)$child;
				}
			}

			$poi->distance = GeoUtil::getGreatCircleDistance(deg2rad($lat), deg2rad($lon), deg2rad($poi->lat), deg2rad($poi->lon));
			if ($poi->distance < $radius + $accuracy) {
				$result[] = $poi;
			}
		}

		libxml_use_internal_errors($libxmlErrorHandlingState);

		return $result;
	}
}
