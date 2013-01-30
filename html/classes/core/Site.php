<?php

	class Site extends Model {

		protected static $instance;

		protected $siteID;
		protected $url;
		protected $siteFolder;
		protected $attributes;

		// constructor / destructor
		public function __construct($hostname = null) {
			$this->attributes = new Map();
			$this->loadSite($hostname);
		}

		protected function __clone() {
		}

		public static function getInstance($hostname) {
		    if (!self::$instance instanceof self) {
		      self::$instance = new self($hostname);
		    }
		    return self::$instance;
		}

		// methods

		public function loadAttributes($includeEmpties = false) {
			$dbConn = DatabaseConnection::getConnection();

			$sql = <<<SQL
			SELECT * FROM attribute a
				LEFT JOIN attribute_site sa
				ON sa.attributeKey = a.`key`
				WHERE a.`type` = 'SITE'
				AND
SQL;
			if($includeEmpties) {
				$sql .= ' (sa.siteID = :siteID OR sa.siteID IS NULL)';
			} else {
				$sql .= ' sa.siteID = :siteID';
			}
			$sql .= ' AND a.siteID = :siteID';

			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('siteID', $this->siteID);

			$attributedata = $stmt->execute();

			for ($i = 0; $i < $attributedata->getRowCount(); $i++) {
				$row = $attributedata->nextRow();
				$tmpAttribute = Attribute::loadAttribute($row);
				// $includeEmpties ? $this->allttributes->add($row['key'], $tmpAttribute) : $this->attributes->add($row['key'], $tmpAttribute);
				$this->attributes->add($row['key'], $tmpAttribute);
			}
		}

		/**
		* Load the site from the database.
		* Returns a site object on success.
		* Throws Exception on connect error.
		*/
		public function loadSite($hostname = null) {

			if(defined('DB_DATABASE')) {
				$dbConn = DatabaseConnection::getConnection();

				$stmt = $dbConn->prepareStatement('SELECT * FROM core_site WHERE url = :url');
				$stmt->bindParameter('url', $hostname);

				try {
					$sitedata = $stmt->execute();

					if ($sitedata->getRowCount() > 0) {
						$row = $sitedata->nextRow();
						$this->siteID = $row['siteID'];
						$this->url = $row['url'];
						$this->siteFolder = $row['siteFolder'];
						$this->loadAttributes();
						return;
					}
				} catch(Exception $e) {
					//should fall through to returning default site with default attributes
				}
			}

			$this->siteID = 0;
			$this->url = '';
			$this->siteFolder = '';

		}

		public function getImage($image, $id = '', $title = '', $xhtml = true) {
			if ($id != '') $idtag = ' id="' . $id . '"'; else $idtag = '';
			if ($title != '') $titletag = ' title="' . $title . '"'; else $titletag = '';
			if ($xhtml) $xhtmltag = ' /';
			return '<img src="' . PLATFORM_SITE_IMAGES . $image . '"' . $idtag . $titletag . $xhtmltag . ">";
		}

		public function getDBImage($image, $id = '', $title = '', $xhtml = true) {
			if ($id != '') $idtag = ' id="' . $id . '"'; else $idtag = '';
			if ($title != '') $titletag = ' title="' . $title . '"'; else $titletag = '';
			if ($xhtml) $xhtmltag = ' /';
			return '<img src="' . PLATFORM_SITE_DBIMAGES . $image . '"' . $idtag . $titletag . $xhtmltag . ">";
		}

		// TODO Query James on whether or not I should be updating both $attributes and $allAttributes
		public function setAttribute(Attribute $attribute) {
			$this->attributes->add($attribute->getKey(), $attribute,true);
		}

		public function saveAttributes() {
			$dbConn = DatabaseConnection::getConnection();

			$sql = 'REPLACE INTO attribute_site (siteID, attributeKey, `value`) VALUES ';
			$tmp = array();
			$keys = array();
			$values = array();
			$emptyAttrs = array();

			$i = 0;
			foreach($this->attributes as $attribute) {
				if($attribute->getValue() != '') {
					$tmp[] = '(' . $this->siteID . ', :attKey' . $i . ', :val' . $i . ')';
					$keys[$i] = $attribute->getKey();
					$values[$i] = $attribute->getValue();
					++$i;
			 	} else {
			 		$emptyAttrs[] = $attribute->getKey();
			 	}
			}
			$sql .= implode(', ', $tmp);

			$stmt = $dbConn->prepareStatement($sql);
			for($j=0; $j<$i; ++$j) {
				$stmt->bindParameter('attKey' . $j, $keys[$j]);
				$stmt->bindParameter('val' . $j, $values[$j]);
			}
			$stmt->execute();

			if(!empty($emptyAttrs)) {
				$sql = 'DELETE FROM attribute_site WHERE siteID = :siteID AND attributeKey ';

				$sql .= (count($emptyAttrs) > 1) ? 'IN (' : '= ';
				foreach ($emptyAttrs as $attr)
					$sql .= ':' . $attr . ', ';
				$sql = substr($sql, 0, -2); // Trim the trailing comma and whitespace
				$sql .= (count($emptyAttrs) > 1 ? ')' : '');

				$stmt = $dbConn->prepareStatement($sql);
				$stmt->bindParameter('siteID', $this->getSiteID());
				foreach ($emptyAttrs as $attr)
					$stmt->bindParameter($attr, $attr);
				$stmt->execute();
			}

		}

		public function toXML($xmlparent, $includeAttributes = false) {
			$xmlsite = $xmlparent->addChild('Site');

			$xmlsite->addChild('siteID', htmlentities($this->getSiteID()));
			$xmlsite->addChild('url', htmlentities($this->getUrl()));
			$xmlsite->addChild('siteTitle', htmlentities(TRAINER_TITLE));
			$xmlsite->addChild('language', htmlentities($this->getLanguage()));
			$xmlsite->addChild('country', htmlentities($this->getCountry()));

			if ($includeAttributes) {
				$xmlattributes = $xmlsite->addChild('Attributes');
				foreach ($this->getAttributes() as $attribute) {
					$attribute->toXML($xmlattributes);
				}
			}

			return $xmlsite;
		}

	}

	/* --- SiteException --- */

	class SiteException extends Exception {

		public function SiteException($message) {
			parent::__construct($message);
		}

	}

?>