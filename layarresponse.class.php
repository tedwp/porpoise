<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Response for Layar
 *
 * @package PorPOISe
 */

/**
 * Response object
 *
 * Contains only overall response parameters, not specific ones
 * such as errorCode, errorMessage, nextPageKey and hasMorePages
 *
 * @package PorPOISe
 */
class LayarResponse {
	/** @var POI[] */
	public $hotspots = array();
	/** @var int Radius containing the returned POI set */
	public $radius;
	/** @var int Refresh interval in seconds */
	public $refreshInterval = NULL;
	/** @var int Refresh distance in meters */
	public $refreshDistance = NULL;
	/** @var bool Do a full refresh or an update */
	public $fullRefresh = TRUE;
	/** @var string Response message to display */
	public $responseMessage = NULL;
	/** @var Action */
	public $action = NULL;
}
