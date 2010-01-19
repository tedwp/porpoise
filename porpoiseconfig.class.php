<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 */

/**
 * PorPOISe configuration class
 *
 * @package PorPOISe
 */

/**
 * This class holds global PorPOISe configuration
 *
 * Contains developer key and ID, as well as layer definitions
 *
 * @package PorPOISe
 */
class PorPOISeConfig {
	/** @var string Source file of configuration */
	protected $source;

	/** @var string Developer key */
	public $developerKey;
	/** @var int Developer ID */
	public $developerID;
	/** @var LayerDefinition[] Layers */
	public $layerDefinitions;

	/**
	 * Constructor
	 *
	 * @param string $source Source to load from (filename or XML)
	 * @param bool $fromString $source is an XML string, not a filename. Default FALSE
	 */
	public function __construct($source = NULL, $fromString = FALSE) {
		$this->layerDefinitions = array();
		if (!empty($source)) {
			$this->load($source, $fromString);
		}
	}

	/**
	 * Load config from XML
	 *
	 * @param string $source Filename or XML string
	 * @param bool $fromString $source is an XML string, not a filename. Default FALSE
	 *
	 * @return void
	 * @throws Exception on XML failure
	 */
	public function load($source, $fromString = FALSE) {
		$this->source = $source;

		$config = new SimpleXMLElement($this->source, 0, !$fromString);
		$this->developerID = (string)$config->{"developer-id"};
		$this->developerKey = (string)$config->{"developer-key"};
		foreach ($config->xpath("layers/layer") as $child) {
			$def = new LayerDefinition();
			$def->name = (string)$child->name;
			$def->collector = (string)$child->collector;
			if (isset($child->source->dsn)) {
				$def->setSourceType(LayerDefinition::DSN);
				$def->source["dsn"] = (string)$child->source->dsn;
				if (isset($child->source->username)) {
					$def->source["username"] = (string)$child->source->username;
				}
				if (isset($child->source->password)) {
					$def->source["password"] = (string)$child->source->password;
				}
			} else {
				$def->source = (string)$child->source;
			}
			$this->layerDefinitions[] = $def;
		}
	}

	/**
	 * Save config to XML
	 *
	 * @param bool $asString Return XML as string instead of saving to file
	 *
	 * @return mixed Number of bytes written when writing to a file, XML
	 * string when saveing as a string. FALSE on failure
	 */
	public function save($asString = FALSE) {
		$dom = new DOMDocument("1.0", "UTF-8");
		$dom->formatOutput = TRUE;

		$root = $dom->createElement("porpoise-configuration");
		$dom->appendChild($root);
		
		$id = $dom->createElement("developer-id", $this->developerID);
		$root->appendChild($id);
		$key = $dom->createElement("developer-key", $this->developerKey);
		$root->appendChild($key);
		$layers = $dom->createElement("layers");
		$root->appendChild($layers);
		foreach ($this->layerDefinitions as $layerDefinition) {
			$layer = $dom->createElement("layer");
			$layers->appendChild($layer);
			$name = $dom->createElement("name", $layerDefinition->name);
			$layer->appendChild($name);
			$collector = $dom->createElement("collector", $layerDefinition->collector);
			$layer->appendChild($collector);
			$source = $dom->createElement("source");
			$layer->appendChild($source);
			switch($layerDefinition->getSourceType()) {
			case LayerDefinition::DSN:
				$dsn = $dom->createElement("dsn", $layerDefinition->source["dsn"]);
				$source->appendChild($dsn);
				$username = $dom->createElement("username", $layerDefinition->source["username"]);
				$source->appendChild($username);
				$password = $dom->createElement("password", $layerDefinition->source["password"]);
				$source->appendChild($password);
				break;
			case LayerDefinition::FILE:
				$filename = $dom->createTextNode($layerDefinition->source);
				$source->appendChild($filename);
				break;
			default:
				throw new Exception(sprintf("Invalid source type in configuration: %d\n", $layerDefinition->getSourceType()));
			}
		}

		if ($asString) {
			return $dom->saveXML();
		} else {
			return $dom->save($this->source);
		}
	}
}

/**
 * Class for holding a layer definition
 *
 * @package PorPOISe
 */
class LayerDefinition {
	/** Magic number to indicate source is a file */
	const FILE = 1;
	/** Magic number to indicate source is a DSN */
	const DSN = 2;

	/** @var string Layer name */
	public $name;
	/** @var mixed Layer source */
	public $source;
	/** @var string Name of collector class */
	public $collector;

	/** @var int Source type */
	protected $sourceType = self::FILE;

	/**
	 * Set source type of this layer
	 *
	 * Valid values are LayerDefinition::FILE and LayerDefinition::DSN. Resets
	 * the current source value.
	 *
	 * @param int $type
	 *
	 * @return void
	 */
	public function setSourceType($type) {
		$this->sourceType = $type;
		switch ($this->sourceType) {
		case self::DSN:
			$this->source = array("dsn" => NULL, "username" => NULL, "password" => NULL);
			break;
		case self::FILE:
			$this->source = NULL;
			break;
		default:
			throw new Exception(sprintf("Invalid source type for layer: %d\n", $type));
		}
	}

	/**
	 * Get source type
	 *
	 * Returns the value set by setSourceType() or a default value if nothing
	 * has been explicitly set.
	 *
	 * @return int
	 */
	public function getSourceType() {
		return $this->sourceType;
	}
}
