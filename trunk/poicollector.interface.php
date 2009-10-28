<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * File for abstract POI collector
 *
 * @package PorPOISe
 */

/**
 * POI collector interface
 *
 * A POI collector is in charge of getting POIs from a specified source.
 * Each specific POI collector should be able to open (connect to) the
 * specified source and get POI information from it somehow.
 *
 * @package PorPOISe
 */
interface POICollector {
	/**
	 * Constructor
	 *
	 * Providing a source is a minimal requirement
	 *
	 * @param string $source
	 */
	public function __construct($source);

	/**
	 * Return POIs
	 *
	 * This method MAY use the parameters to restrict the returned result,
	 * but this is not a guarantee. Final filtering will always have to
	 * happen by the caller.
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
	public function getPOIs($lat, $lon, $radius, $accuracy, $options);
}
