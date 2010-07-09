<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 *
 * Acknowledgments:
 * Guillaume Danielou of kew.org for the XSL transformation
 */

/**
 * POI connector from XML files
 *
 * @package PorPOISe
 */

/**
 * Requires POI class
 */
require_once("poi.class.php");

/**
 * Requires POIConnector class
 */
require_once("poiconnector.class.php");
/**
 * Requires GeoUtil
 */
require_once("geoutil.class.php");

/**
 * POI connector from XML files
 *
 * @package PorPOISe
 */
class XMLPOIConnector extends POIConnector {
	const EMPTY_DOCUMENT = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<pois/>";
	/** @var string */
	protected $source;
	/** @var string */
	protected $styleSheetPath = "";

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
	 * Set the path of an XSL style sheet to transform the input XML
	 *
	 * @param string $styleSheetPath
	 * @return void
	 */
	public function setStyleSheet($styleSheetPath) {
		$this->styleSheetPath = $styleSheetPath;
	}

	/**
	 * Provides an XPath query for finding POIs in the source file.
	 *
	 * For relative queries, the context node is the root element.
	 * This method can be overridden to use a different query.
	 *
	 * @param Filter $filter
	 * 
	 * @return string
	 */
	public function buildQuery(Filter $filter = NULL) {
		return "poi";
	}

	/**
	 * Get POIs
	 *
	 * @param Filter $filter
	 *
	 * @return POI[]
	 *
	 * @throws Exception
	 */
	public function getPOIs(Filter $filter = NULL) {
		$libxmlErrorHandlingState = libxml_use_internal_errors(TRUE);

		$lat = $filter->lat;
		$lon = $filter->lon;
		$radius = $filter->radius;
		$accuracy = $filter->accuracy;

		if(!empty($this->styleSheetPath)) {
			$simpleXML = new SimpleXMLElement($this->transformXML(), 0, FALSE);
		} else {
			$simpleXML = new SimpleXMLElement($this->source, 0, TRUE);
		}
		if (empty($simpleXML)) {
			throw new Exception("Failed to load data");
		}

		$result = array();

		$xpathQuery = $this->buildQuery($filter);
		foreach ($simpleXML->xpath($xpathQuery) as $poiData) {
			if (empty($poiData->dimension) || (int)$poiData->dimension == 1) {
				$poi = new POI1D();
			} else if ((int)$poiData->dimension == 2) {
				$poi = new POI2D();
			} else if ((int)$poiData->dimension == 3) {
				$poi = new POI3D();
			} else {
				throw new Exception("Invalid dimension: " . (string)$poiData->dimension);
			}
			foreach ($poiData->children() as $child) {
				$nodeName = $child->getName();
				if ($nodeName == "action") {
					$poi->actions[] = new POIAction($child);
				} else if ($nodeName == "object") {
					$poi->object = new POIObject($child);
				} else if ($nodeName == "transform") {
					$poi->transform = new POITransform($child);
				} else {
					switch($nodeName) {
					case "dimension":
					case "type":
					case "alt":
					case "relativeAlt":
						$value = (int)$child;
						break;
					case "lat":
					case "lon":
						$value = (float)$child;
						break;
					case "showSmallBiw":
					case "showBiwOnClick":
					case "doNotIndex":
						$value = (bool)(string)$child;
						break;
					default:
						$value = (string)$child;
						break;
					}
					$poi->$nodeName = $value;
				}
			}
			if (empty($filter)) {
				$result[] = $poi;
			} else if (!empty($filter->requestedPoiId) && $filter->requestedPoiId == $poi["id"]) {
				// always return the requested POI at the top of the list to
				// prevent cutoff by the 50 POI response limit
				array_unshift($result, $poi);
			} else {
				$poi->distance = GeoUtil::getGreatCircleDistance(deg2rad($lat), deg2rad($lon), deg2rad($poi->lat), deg2rad($poi->lon));
				if ((empty($radius) || $poi->distance < $radius + $accuracy) && $this->passesFilter($poi, $filter)) {
					$result[] = $poi;
				}
			}
		}

		libxml_use_internal_errors($libxmlErrorHandlingState);

		return $result;
	}

	/**
	 * Store POIs
	 *
	 * Builds up an XML and writes it to the source file with which this
	 * XMLPOIConnector was created. Note that there is no way to do
	 * "reverse XSL" so any stylesheet is ignored and native PorPOISe XML
	 * is written to the source file. If this file is not writable, this
	 * method will return FALSE.
	 *
	 * @param POI[] $pois
	 * @param string $mode "update" or "replace"
	 * @param bool $asString Return XML as string instead of writing it to file
	 * @return mixed FALSE on failure, TRUE or a string on success
	 */
	public function storePOIs(array $pois, $mode = "update", $asString = FALSE) {
		$libxmlErrorHandlingState = libxml_use_internal_errors(TRUE);

		// keep track of the highest id
		$maxID = 0;

		// initialize result XML
		if ($mode == "update") {
			$simpleXML = new SimpleXMLElement($this->source, 0, TRUE);
			if (empty($simpleXML)) {
				throw new Exception("Failed to load data file");
			}
			// look for highest id in current set
			$idNodes = $simpleXML->xpath("//poi/id");
			foreach ($idNodes as $idNode) {
				$id = (int)$idNode;
				if ($id > $maxID) {
					$maxID = $id;
				}
			}
		} else if ($mode == "replace") {
			$simpleXML = new SimpleXMLElement(self::EMPTY_DOCUMENT);
			// $maxID stays at 0 for now
		}
		$domXML = dom_import_simplexml($simpleXML);
		// look for high id in new set, see if it's higher than $maxID
		foreach ($pois as $poi) {
			if ($poi->id > $maxID) {
				$maxID = $poi->id;
			}
		}
		
		// add POIs to result
		foreach($pois as $poi) {
			// see if POI is old or new
			if (empty($poi->id)) {
				// assign new id
				$poi->id = $maxID + 1;
				$maxID = $poi->id;
				$oldSimpleXMLElements = array();
			} else {
				// look for existing POI with this id
				$oldSimpleXMLElements = $simpleXML->xpath("//poi[id=" . $poi->id . "]");
			}
			// build element and convert to DOM
			//$simpleXMLElement = self::arrayToSimpleXMLElement("poi", $poi->toArray());
			$simpleXMLElement = self::poiToSimpleXMLElement($poi);
			$domElement = $domXML->ownerDocument->importNode(dom_import_simplexml($simpleXMLElement), TRUE);
			if (empty($oldSimpleXMLElements)) {
				$domXML->appendChild($domElement);
			} else {
				$domXML->replaceChild($domElement, dom_import_simplexml($oldSimpleXMLElements[0]));
			}				
		}

		if ($asString) {
			return $simpleXML->asXML();
		} else {
			// write new dataset to file
			return $simpleXML->asXML($this->source);
		}

		libxml_use_internal_errors($libxmlErrorHandlingState);
	}

	/**
	 * Delete a POI
	 *
	 * @param string $poiID ID of the POI to delete
	 *
	 * @return void
	 *
	 * @throws Exception If the source is invalid or the POI could not be deleted
	 */
	public function deletePOI($poiID) {
		$libxmlErrorHandlingState = libxml_use_internal_errors(TRUE);

		$dom = new DOMDocument();
		$dom->load($this->source);
		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query(sprintf("//poi[id='%s']", $poiID));
		if ($nodes->length == 0) {
			throw new Exception(sprintf("Could not delete POI: no POI found with ID %s", $poiID));
		}
		$nodesToRemove = array();
		for ($i = 0; $i < $nodes->length; $i++) {
			$nodesToRemove[] = $nodes->item($i);
		}
		foreach ($nodesToRemove as $node) {
			$node->parentNode->removeChild($node);
		}

		$dom->save($this->source);

		libxml_use_internal_errors($libxmlErrorHandlingState);
	}

	/**
	 * Convert an array to a SimpleXMLElement
	 *
	 * Converts $array to a SimpleXMLElement by mapping they array's keys
	 * to node names and values to values. Traverses sub-arrays.
	 *
	 * @param string $rootName The name of the root element
	 * @param array $array The array to convert
	 * @return SimpleXMLElement
	 */
	public static function arrayToSimpleXMLElement($rootName, array $array) {
		$result = new SimpleXMLElement(sprintf("<%s/>", $rootName));
		self::addArrayToSimpleXMLElement($result, $array);
		return $result;
	}

	/**
	 * Recursive helper method for arrayToSimpleXMLElement
	 *
	 * @param SimpleXMLElement $element
	 * @param array $array
	 *
	 * @return void
	 */
	public static function addArrayToSimpleXMLElement(SimpleXMLElement $element, array $array) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$child = $element->addChild($key);
				self::addArrayToSimpleXMLElement($child, $value);
			} else {
				$element->addChild($key, $value);
			}
		}
	}

	/**
	 * Create a SimpleXMLElement representation of a POI
	 *
	 * @param POI $poi
	 * @return SimpleXMLElement
	 */
	public static function poiToSimpleXMLElement(POI $poi) {
		$poiElement = new SimpleXMLElement("<" . "?xml version=\"1.0\" encoding=\"UTF-8\"?" . ">\n<poi/>");
		foreach ($poi as $key => $value) {
			if ($key == "actions") {
				foreach ($value as $action) {
					$actionElement = $poiElement->addChild("action");
					$actionElement->addChild("uri", str_replace("&", "&amp;", $action->uri));
					$actionElement->addChild("label", str_replace("&", "&amp;", $action->label));
/** @todo: add all the 4.1 extra actions */
					if (!empty($action->autoTriggerRange)) {
						$actionElement->addChild("autoTriggerRange", str_replace("&", "&amp;", $action->autoTriggerRange));
						$actionElement->addChild("autoTriggerOnly", str_replace("&", "&amp;", $action->autoTriggerOnly));
					}
				}
			} else if ($key == "transform") {
				$transformElement = $poiElement->addChild("transform");
				foreach(array("rel", "angle", "scale") as $elementName) {
					$transformElement->addChild($elementName, str_replace("&", "&amp;", $poi->$elementName));
				}
			} else if ($key == "object") {
				$objectElement = $poiElement->addChild("object");
				foreach(array("baseURL", "full", "reduced", "icon", "size") as $elementName) {
					$objectElement->addChild($elementName, str_replace("&", "&amp;", $poi->$elementName));
				}
			} else {
				$poiElement->addChild($key, str_replace("&", "&amp;", $value));
			}
		}
		return $poiElement;
	}

	/**
	 * Transform the input source using the set XSL stylesheet
	 *
	 * @return string The resulting XML
	 */
	public function transformXML() {
		$xslProcessor = new XSLTProcessor();
		$xsl = new DOMDocument();    
		if ($xsl->load($this->styleSheetPath) == FALSE) {
			throw new Exception("transformXML - Failed to load stylesheet");
		}
		$xslProcessor->importStyleSheet($xsl);   
		$xml = new DOMDocument();
		if ($xml->load($this->source) == FALSE) {
			throw new Exception("transformXML - Failed to load xml");
		}
		return $xslProcessor->transformToXml($xml);
	}

	/**
	 * Set an option
	 *
	 * XMLPOIConnector supports one option, "stylesheet"
	 *
	 * @param string $optionName
	 * @param string $optionValue
	 *
	 * @return void
	 */
	public function setOption($optionName, $optionValue) {
		switch ($optionName) {
		case "stylesheet":
			$this->setStyleSheet($optionValue);
			break;
		default:
			parent::setOption($optionName, $optionValue);
			break;
		}
	}
}
