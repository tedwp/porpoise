<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * Classes of Point of Interest definition
 *
 * @package PorPOISe
 */

/**
 * Subclasses of this class can all be converted to (associative) arrays
 * (useful for a.o. JSON-ing).
 *
 * @package PorPOISe
 */
abstract class Arrayable {
	/**
	 * Stores the contents of this object into an associative array
	 * with elements named after the members of the object. Members that
	 * contain properties are converted recursively.
	 *
	 * @return array
	 */
	public function toArray() {
		$result = array();
		$reflectionClass = new ReflectionClass($this);
		$reflectionProperties = $reflectionClass->getProperties();
		foreach ($reflectionProperties as $reflectionProperty) {
			$propertyName = $reflectionProperty->getName();
			$result[$propertyName] = $this->$propertyName;
			if (is_object($result[$propertyName])) {
				$result[$propertyName] = $result[$propertyName]->toArray();
			}
		}
		return $result;
	}
}

/**
 * Class to store a POI action
 *
 * @package PorPOISe
 */
class POIAction extends Arrayable {
	static public $defaultActionLabel = "Do something funky";

	public $uri = NULL;
	public $label = NULL;

	/**
	 * Constructor
	 *
	 * If $source is a string, it must be a URI and a default label will be
	 * assigned to it
	 * If $source is an array it is expected to contain elements "label"
	 * and "uri".
	 * If $source is an object, it is expected to have members "label" and
	 * "uri".
	 *
	 * @param mixed $source
	 */
	public function __construct($source = NULL) {
		if (empty($source)) {
			return;
		}

		if (is_string($source)) {
			$this->label = self::$defaultActionLabel;
			$this->uri = $source;
		} else if (is_array($source)) {
			$this->label = $source["label"];
			$this->uri = $source["uri"];
		} else {
			$this->label = $source->label;
			$this->uri = $source->uri;
		}
	}
}

/**
 * Class for storing POI information
 *
 * @package PorPOISe
 */
class POI extends Arrayable {
	public $actions = array();
	public $attribution = NULL;
	public $distance = NULL;
	public $id = NULL;
	public $imageURL = NULL;
	public $lat = NULL;
	public $lon = NULL;
	public $line2 = NULL;
	public $line3 = NULL;
	public $line4 = NULL;
	public $title = NULL;
	public $type = NULL;

	/**
	 * Constructor
	 *
	 * $source is expected to be an array or an object, with element/member
	 * names corresponding to the member names of POI. This allows both
	 * constructing from an associatiev array as well as copy constructing.
	 *
	 * @param mixed $source
	 */
	public function __construct($source = NULL) {
		if (!empty($source)) {
			$reflectionClass = new ReflectionClass($this);
			$reflectionProperties = $reflectionClass->getProperties();
			foreach ($reflectionProperties as $reflectionProperty) {
				$propertyName = $reflectionProperty->getName();
				if (is_array($source)) {
					if (isset($source[$propertyName])) {
						// $this->actions must be an array,
						// we only allow one action to be set through construction
						// from an array
						if ($propertyName == "actions") {
							$value = array(new POIAction($source[$propertyName]));
						} else {
							$value = $source[$propertyName];
						}
						$this->$propertyName = $value;
					}
				} else {
					if (isset($source->$propertyName)) {
						if ($propertyName == "actions") {
							$value = array();
							foreach ($source->actions as $sourceAction) {
								$value[] = new POIAction($sourceAction);
							}
						} else {
							$value = $source->$propertyName;
						}
						$this->$propertyName = $value;
					}
				}
			}
		}
	}
}
