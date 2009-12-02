<?php

/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Released under a permissive license (see LICENSE)
 *
 * Acknowledgments:
 * Jerouris for the UTF-8 fix
 */

/**
 * POI collector from SQL databases
 *
 * @package PorPOISe
 */

/**
 * Requires POI classes
 */
require_once("poi.class.php");

/**
 * Requires POICollector interface
 */
require_once("poicollector.interface.php");

/**
 * Requires GeoUtils
 */
require_once("geoutil.class.php");

/**
 * POI collector from SQL databases
 *
 * @package PorPOISe
 */
class SQLPOICollector implements POICollector {
	/** @var string DSN */
	protected $source;
	/** @var string username */
	protected $username;
	/** @var password */
	protected $password;
	/** @var PDO PDO instance */
	protected $pdo;

	/**
	 * Constructor
	 *
	 * The field separator can be configured by modifying the public
	 * member $separator.
	 *
	 * @param string $source DSN of the database
	 * @param string $username Username to access the database
	 * @param string $password Password to go with the username
	 */
	public function __construct($source, $username = "", $password = "") {
		$this->source = $source;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Get PDO instance
	 *
	 * @return PDO
	 */
	protected function getPDO() {
		if (empty($this->pdo)) {
			$this->pdo = new PDO ($this->source, $this->username, $this->password);
			// force UTF-8 (Layar talks UTF-8 and nothing else)
			try {
				$driverName = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
				if ($driverName == "mysql") {
					$sql = "SET NAMES 'utf8'";
					$stmt = $this->pdo->prepare($sql);
					$stmt->execute();
				}
			} catch (PDOException $e) {
				// driver doesn't support getAttribute
			}
		}
		return $this->pdo;
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
		try {
			$pdo = $this->getPDO();
			$sql = "SELECT *, " . GeoUtil::EARTH_RADIUS . " * 2 * asin(
				sqrt(
					pow(sin((radians(" . addslashes($lat) . ") - radians(lat)) / 2), 2)
					+
					cos(radians(" . addslashes($lat) . ")) * cos(radians(lat)) * pow(sin((radians(" . addslashes($lon) . ") - radians(lon)) / 2), 2)
				)
			) AS distance
			FROM POI
			HAVING distance < (" . addslashes($radius) . " + " . addslashes($accuracy) . ")
			";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$result = array();
			while ($row = $stmt->fetch()) {
				$result[] = new POI($row);
			}
			foreach ($result as $poi) {
				$sql = "SELECT * FROM Action WHERE poiID=?";
				$stmt = $pdo->prepare($sql);
				$stmt->execute(array($poi->id));
				$poi->actions = array();
				while ($row = $stmt->fetch()) {
					$poi->actions[] = new POIAction($row);
				}
			}
			return $result;
		} catch (PDOException $e) {
			throw new Exception("Database error: " . $e->getMessage());
		}
	}
}
