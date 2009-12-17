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
 * specified source and get POI information from it somehow. A POICollector
 * is also in charge of storing POIs in the same format at the same source.
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

	/**
	 * Store POIs
	 *
	 * Store a set of POIs
	 *
	 * @param POI[] $pois POIs to store
	 * @param string $mode "replace" to replace the current set, "update" to update current set (default)
	 * @return void
	 */
	public function storePOIs(array $pois, $mode = "update");

	/**
	 * Delete a POI
	 *
	 * @param string $poiID ID of the POI to delete
	 *
	 * @return void
	 *
	 * @throws Exception If the source is invalid or the POI could not be deleted
	 */
	public function deletePOI($poiID);
}
