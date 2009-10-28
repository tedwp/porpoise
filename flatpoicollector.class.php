<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * POI collector from "flat" files
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
 * POI collector from "flat" files
 *
 * @package PorPOISe
 */
class FlatPOICollector implements POICollector {
	/** @var string */
	protected $source;
	/** @var string */
	public $separator = "\t";

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
		$file = @file($this->source);
		if (empty($file)) {
			throw new Exception("File not readable or empty");
		}

		$result = array();
		$headers = explode($this->separator, trim($file[0], "\r\n"));
		for ($i = 1; $i < count($file); $i++) {
			$line = trim($file[$i], "\r\n");
			if (empty($line)) {
				continue;
			}
			$fields = explode($this->separator, $line);

			$poi = new POI(array_combine($headers, $fields));
			$poi->distance = GeoUtil::getGreatCircleDistance(deg2rad($lat), deg2rad($lon), deg2rad($poi->lat), deg2rad($poi->lon));
			if ($poi->distance < $radius + $accuracy) {
				$result[] = $poi;
			}
		}

		return $result;
	}
}
