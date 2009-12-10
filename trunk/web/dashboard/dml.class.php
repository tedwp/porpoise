<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * PorPOISe Dashboard Data Management Layer
 *
 * @package PorPOISe
 * @subpackage Dashboard
 */

/* change working directory to root of PorPOISe */
chdir(dirname(__FILE__) . "/../..");
/** Configuration class */
require_once("porpoise.inc.php");

/**
 * Data Management Layer class
 *
 * @package PorPOISe
 * @subpackage Dashboard
 */
class DML {
	/**
	 * Get configuration
	 *
	 * @return PorPOISeConfig
	 */
	static public function getConfiguration() {
		return new PorPOISeConfig("config.xml");
	}

	/**
	 * Get POIs for a configured layer
	 *
	 * @param string $layerName
	 * @return POI[] FALSE if the layer is unknown
	 */
	static public function getPOIs($layerName) {
		$layerDefinition = self::getLayerDefinitionByLayerName($layerName);
		if ($layerDefinition == NULL) {
			return FALSE;
		}
		switch($layerDefinition->getSourceType()) {
		case LayerDefinition::DSN:
			$poiCollector = new $layerDefinition->collector($layerDefinition->source["dsn"], $layerDefinition->source["username"], $layerDefinition["password"]);
			break;
		case LayerDefinition::FILE:
			$poiCollector = new $layerDefinition->collector($layerDefinition->source);
			break;
		default:
			throw new Exception(sprintf("Invalid source type: %d", $layerDefinition->getSourceType()));
		}
		return $poiCollector->getPOIs(0,0,0,0,array());
	}

	/**
	 * Get a single POI
	 *
	 * @param string $layerName The layer
	 * @param string $poiID The POI to look for
	 *
	 * @return POI
	 *
	 * @todo Rewrite as soon as POICollectors support getPOI
	 */
	static public function getPOI($layerName, $poiID) {
		$pois = self::getPOIs($layerName);
		if (empty($pois)) {
			return NULL;
		}
		foreach ($pois as $poi) {
			if ($poi->id == $poiID) {
				return $poi;
			}
		}
		return NULL;
	}

	/**
	 * Check for validity of UN/PW combination
	 *
	 * @param string $username
	 * @param string $password In unencrypted form
	 *
	 * @return bool TRUE if combination is valid, FALSE otherwise
	 */
	public function validCredentials($username, $password) {
		if (empty($GLOBALS["_access"][$username])) {
			return FALSE;
		}
		return ($GLOBALS["_access"][$username] === crypt($password, $GLOBALS["_access"][$username]));
	}

	/**
	 * Get a LayerDefinition from the configuration
	 *
	 * @param string $layerName
	 *
	 * @return LayerDefinition or NULL if the layer was not found
	 */
	public static function getLayerDefinitionByLayerName($layerName) {
		$config = self::getConfiguration();
		foreach ($config->layerDefinitions as $layerDefinition) {
			if ($layerDefinition->name == $layerName) {
				return $layerDefinition;
			}
		}
		return NULL;
	}
}
