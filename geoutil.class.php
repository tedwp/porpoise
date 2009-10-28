<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Geographic utilities class
 *
 * @package PorPOISe
 */

class GeoUtil {
	// source: http://en.wikipedia.org/wiki/Great-circle_distance
	const EARTH_RADIUS = 6371010;	// meters

	/**
	 * Calculate the great-circle distance between 2 points on earth
	 *
	 * The great-circle distance is the minimal distance between two points
	 * on a sphere, calculated over the surface of the sphere. Does not work
	 * accurately for antipodal (opposite) points, but that's no problem for
	 * this application.
	 * Source: http://en.wikipedia.org/wiki/Great-circle_distance
	 *
	 * @param float $lat1 Latitude of point 1 in degrees
	 * @param float $lon1 Longitude of point 1 in degrees
	 * @param float $lat2 Latitude of point 2 in degrees
	 * @param float $lon2 Longitude of point 2 in degrees
	 * @param bool $inputInDegrees Set to true of the input coordinates are
	 *             in degrees
	 *
	 * @return float
	 */
	public static function getGreatCircleDistance($lat1, $lon1, $lat2, $lon2, $inputInDegrees = FALSE) {
		if ($inputInDegrees) {
			// convert lat and lon to radians
			$lat1 = deg2rad($lat1);
			$lon1 = deg2rad($lon1);
			$lat2 = deg2rad($lat2);
			$lon2 = deg2rad($lon2);
		}

		// this method gleaned from Wikipedia
		// http://en.wikipedia.org/wiki/Great-circle_distance
		$deltaLat = $lat1 - $lat2;
		$deltaLon = $lon1 - $lon2;
		$deltaSigma = 2 * asin(
			sqrt(
				pow(sin($deltaLat / 2), 2)
				+
				cos($lat1) * cos($lat2) * pow(sin($deltaLon / 2), 2)
			)
		);
		$result = self::EARTH_RADIUS * $deltaSigma;
		return $result;
	}
}
