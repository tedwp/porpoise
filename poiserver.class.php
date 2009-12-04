<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 *
 * Acknowledgments:
 * Robert Harm for the "Increase range" error message
 */

/**
 * POI Server for Layar
 *
 * The server consists of a server class whose objects serve Layar responses when
 * properly configured and a factory class that helps you create a properly
 * configured server.
 *
 * @package PorPOISe
 */

/** Requires POI definition */
require_once("poi.class.php");
/** Requires Layer definition */
require_once("layer.class.php");
/** Requires FlatPOICollector */
require_once("flatpoicollector.class.php");
/** Requires FlatPOICollector */
require_once("xmlpoicollector.class.php");
/** Requires SQLPOICollector */
require_once("sqlpoicollector.class.php");

/**
 * Server class that serves up POIs for Layar
 *
 * @package PorPOISe
 */
class LayarPOIServer {
	/** @const int Default error code */
	const ERROR_CODE_DEFAULT = 20;
	/** @const int Request has no POIs in result */
	const ERROR_CODE_NO_POIS = 21;

	/** @var string[] Error messages stored in an array because class constants cannot be arrays */
	protected static $ERROR_MESSAGES = array(
		self::ERROR_CODE_DEFAULT => "An error occurred"
		, self::ERROR_CODE_NO_POIS => "No POIs nearby. Increase range to see POIs"
	);

	// layers in this server
	protected $layers = array();

	protected $requiredFields = array("userId", "developerId", "developerHash", "timestamp", "layerName", "lat", "lon", "accuracy");
	protected $optionalFields = array("RADIOLIST", "SEARCHBOX_1", "SEARCHBOX_2", "SEARCHBOX_3", "CUSTOM_SLIDER_1", "CUSTOM_SLIDER_2", "CUSTOM_SLIDER_3", "pageKey", "oath_consumer_key", "oauth_signature_method", "oauth_timestamp", "oauth_nonce", "oauth_version", "oauth_signature", "radius", "alt");

	/**
	 * Add a layer to the server
	 *
	 * @param Layer $layer
	 *
	 * @return void
	 */
	public function addLayer(Layer $layer) {
		$this->layers[$layer->layerName] = $layer;
	}

	/**
	 * Handle a request
	 *
	 * Request variables are expected to live in the $_REQUEST superglobal
	 *
	 * @return void
	 */
	public function handleRequest() {
		try {
			$this->validateRequest();
	
			$options = array();
			foreach ($this->optionalFields as $optionalField) {
				$options[$optionalField] = $_REQUEST[$optionalField];
			}
			$layer = $this->layers[$_REQUEST["layerName"]];
			$layer->determineNearbyPOIs($_REQUEST["lat"], $_REQUEST["lon"], $_REQUEST["radius"], $_REQUEST["accuracy"], $options);
			$pois = $layer->getNearbyPOIs();
			if (count($pois) == 0) {
				$this->sendErrorReponse(self::ERROR_CODE_NO_POIS);
				return;
			}
			$morePages = $layer->hasMorePOIs();
			if ($morePages) {
				$nextPageKey = $layer->getNextPageKey();
			} else {
				$nextPageKey = NULL;
			}
		
			$this->sendResponse($pois, $morePages, $nextPageKey);
		} catch (Exception $e) {
			$this->sendErrorResponse(self::ERROR_CODE_DEFAULT, $e->getMessage());
		}
	}

	/**
	 * Send a Layar response to a client
	 *
	 * @param array $pois An array of POIs that match the client's request
	 * @param bool $morePages Pass TRUE if there are more pages beyond this set of POIs
	 * @param string $nextPageKey Pass a valid key if $morePages is TRUE
	 *
	 * @return void
	 */
	protected function sendResponse(array $pois, $morePages = FALSE, $nextPageKey = NULL) {
		$response = array();
		$response["morePages"] = $morePages;
		$response["nextPageKey"] = (string)$nextPageKey;
		$response["layer"] = $_REQUEST["layerName"];
		$response["errorCode"] = 0;
		$response["errorString"] = "ok";
		$response["hotspots"] = array();
		foreach ($pois as $poi) {
			$i = count($response["hotspots"]);
			$response["hotspots"][$i] = $poi->toArray();
			// upscale coordinate values and truncate to int because of inconsistencies in Layar API
			// (requests use floats, reponses use integers?)
			$response["hotspots"][$i]["lat"] = (int)($response["hotspots"][$i]["lat"] * 1000000);
			$response["hotspots"][$i]["lon"] = (int)($response["hotspots"][$i]["lon"] * 1000000);
			// fix some types that are not strings
			$response["hotspots"][$i]["type"] = (int)$response["hotspots"][$i]["type"];
			$response["hotspots"][$i]["distance"] = (float)$response["hotspots"][$i]["distance"];
		}

		printf("%s", json_encode($response));
	}

	/**
	 * Send an error response
	 *
	 * @param int $code Error code for this error
	 * @param string $msg A message detailing what went wrong
	 *
	 * @return void
	 */
	protected function sendErrorResponse($code = self::ERROR_CODE_DEFAULT, $msg = NULL) {
		$response = array();
		if (isset($_REQUEST["layerName"])) {
			$response["layer"] = $_REQUEST["layerName"];
		} else {
			$response["layer"] = "unspecified";
		}
		$response["errorCode"] = $code;
		if (!empty($msg)) {
			$response["errorString"] = $msg;
		} else {
			$response["errorString"] = self::$ERROR_MESSAGES[$code];
		}
		$response["hotspots"] = array();
		$response["nextPageKey"] = NULL;
		$response["morePages"] = FALSE;

		printf("%s", json_encode($response));
	}

	/**
	 * Validate a client request
	 *
	 * If this function returns (i.e. does not throw anything) the request is
	 * valid and can be processed with no further input checking
	 * 
	 * @throws Exception Throws an exception of something is wrong with the request
	 * @return void
	 */
	protected function validateRequest() {
		foreach ($this->requiredFields as $requiredField) {
			if (empty($_REQUEST[$requiredField])) {
				throw new Exception(sprintf("Missing parameter: %s", $requiredField));
			}
		}
		foreach ($this->optionalFields as $optionalField) {
			if (!isset($_REQUEST[$optionalField])) {
				$_REQUEST[$optionalField] = "";
			}
		}

		$layerName = $_REQUEST["layerName"];
		if (empty($this->layers[$layerName])) {
			throw new Exception(sprintf("Unknown layer: %s", $layerName));
		}

		$layer = $this->layers[$layerName];
		if ($layer->developerId != $_REQUEST["developerId"]) {
			throw new Exception(sprintf("Unknown developerId: %s", $_REQUEST["developerId"]));
		}

		if (!$layer->isValidHash($_REQUEST["developerHash"], $_REQUEST["timestamp"])) {
			throw new Exception(sprintf("Invalid developer hash", $_REQUEST["developerHash"]));
		}

		if ($_REQUEST["lat"] < -90 || $_REQUEST["lat"] > 90) {
			throw new Exception(sprintf("Invalid latitude: %s", $_REQUEST["lat"]));
		}

		if ($_REQUEST["lon"] < -180 || $_REQUEST["lon"] > 180) {
			throw new Exception(sprintf("Invalid longitude: %s", $_REQUEST["lon"]));
		}

	}
}

/**
 * Factory class to create LayarPOIServers
 *
 * @package PorPOISe
 */
class LayarPOIServerFactory {
	/** @var $developerId */
	protected $developerId;
	/** @var $developerKey */
	protected $developerKey;

	/**
	 * Constructor
	 *
	 * @param string $developerID Your developer ID
	 * @param string $developerKey Your developer key
	 */
	public function __construct($developerID, $developerKey) {
		$this->developerId = $developerID;
		$this->developerKey = $developerKey;
	}
	
	/**
	 * Create a LayarPOIServer with content from a list of files
	 *
	 * @param array $layerFiles The key of each element is expected to be the
	 * layer's name, the value to be the filename of the file containing the
	 * layer's POI in tab delimited format.
	 *
	 * @return LayarPOIServer
	 */
	public function createLayarPOIServerFromFlatFiles(array $layerFiles) {
		$result = new LayarPOIServer();
		foreach ($layerFiles as $layerName => $layerFile) {
			$layer = new Layer($layerName, $this->developerId, $this->developerKey);
			$poiCollector = new FlatPOICollector($layerFile);
			$layer->setPOICollector($poiCollector);
			$result->addLayer($layer);
		}
		return $result;
	}

	/**
	 * Create a LayarPOIServer with content from a list of XML files
	 *
	 * @param array $layerFiles The key of each element is expected to be the
	 * layer's name, the value to be the filename of the file containing the
	 * layer's POIs in XML format.
	 *
	 * @param string[] $layerFiles
	 * @param string $layerXSL
	 *
	 * @return LayarPOIServer
	 */
	public function createLayarPOIServerFromXMLFiles(array $layerFiles, $layerXSL = "") {
		$result = new LayarPOIServer();
		foreach ($layerFiles as $layerName => $layerFile) {
			$layer = new Layer($layerName, $this->developerId, $this->developerKey);
			$poiCollector = new XMLPOICollector($layerFile);
			$poiCollector->setStyleSheet($layerXSL);
			$layer->setPOICollector($poiCollector);
			$result->addLayer($layer);
		}
		return $result;
	}

	/**
	 * Create a LayarPOIServer with content from a database
	 *
	 * @param array $layerDefinitions The keys of $layerDefinitions define
	 * the names of the created layers, the values should be arrays with
	 * the elements "dsn", "username" and "password" used to connect to the
	 * database. Username and password may be omitted.
	 *
	 * @return LayarPOIServer
	 */
	public function createLayarPOIServerFromDatabase(array $layerDefinitions) {
		$result = new LayarPOIServer();
		foreach ($layerDefinitions as $layerName => $credentials) {
			$layer = new Layer($layerName, $this->developerId, $this->developerKey);
			if (empty($credentials["username"])) {
				$credentials["username"] = "";
			}
			if (empty($credentials["password"])) {
				$credentials["password"] = "";
			}
			$poiCollector = new SQLPOICollector($credentials["dsn"], $credentials["username"], $credentials["password"]);
			$layer->setPOICollector($poiCollector);
			$result->addLayer($layer);
		}

		return $result;
	}

	/**
	 * Create a server based on SimpleXML configuration directives
	 *
	 * $config is an array of SimpleXMLElements, each element should contain
	 * layer nodes specifying collector (class name), layer name and data source.
	 * The root node name is not important but "layers" is suggested.
	 * For flat files and XML, use a URI as source. For SQL, use dsn, username
	 * and password elements.
	 * Example:
	 * <layers>
	 *  <layer>
	 *   <collector>SQLPOICollector</collector>
	 *   <name>test</name>
	 *   <source>
	 *    <dsn>mysql:host=localhost</dsn>
	 *    <username>default</username>
	 *    <password>password</password>
	 *   </source>
	 *  </layer>
	 * </layers>
	 *
	 * @param SimpleXMLElement $config
	 *
	 * @return LayarPOIServer
	 */
	public function createLayarPOIServerFromSimpleXMLConfig(SimpleXMLElement $config) {
		$result = new LayarPOIServer();
		foreach ($config->xpath("layer") as $child) {
			$layer = new Layer((string)$child->name, $this->developerId, $this->developerKey);
			if ((string)$child->collector == "SQLPOICollector") {
				$poiCollector = new SQLPOICollector((string)$child->source->dsn, (string)$child->source->username, (string)$child->source->password);
			} else {
				$collectorName = (string)$child->collector;
				$poiCollector = new $collectorName((string)$child->source);
			}
			$layer->setPOICollector($poiCollector);
			$result->addLayer($layer);
		}
		return $result;
	}
}

