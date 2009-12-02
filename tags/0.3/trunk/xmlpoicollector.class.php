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

		if(!empty($this->styleSheetPath)) {
			$xml = new SimpleXMLElement($this->transformXML(), 0, FALSE);
		} else {
			$xml = new SimpleXMLElement($this->source, 0, TRUE);
		}
		if (empty($xml)) {
			throw new Exception("Failed to load data");
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
}
