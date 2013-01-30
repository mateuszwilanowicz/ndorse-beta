<?php

	/**
	* A class that generate the basic HTML for a webpage.
	*/
	class Page {

		private $title;
		private $pageTemplatePath;
		private $bodyID;
		private $bodyClass;
		private $linkTags = array();
		private $metaTags = array();
		private $stylesheets = array();
		private $internalStylesheets = array();
		private $scripts = array();
		private $headScripts = array();
		private $bodyStartScriptTags = array();
		private $bodyEndScriptTags = array();
		private $page;
		private $data;
		private $xml;


		/**
		* The constructor for the Page class. It sets the values passed to it in the constructor.
		*
		* @param title String The title for the page
		* @param pageTemplatePath The path to the template for this page
		*/
		public function __construct($title, $pageTemplatePath = "") {
			$this->title = $title;
			$this->pageTemplatePath = $pageTemplatePath;
			$this->metaTags[0] = array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8');
			$this->metaTags['author'] = array('content' => 'Route Digital Limited');
			$this->page = $this;
		}

		public function __destruct() {
			unset($this->page); // Remove the circular reference.
		}

		/**
		 * Magic function for returning variables. Will check to see if there
		 * is an override (of the form "get" + Variable_name) first.
		 */
		public function __get($property) {
			// check for an override
			$function = 'get' . ucfirst($property);
			if (method_exists($this, $function)) {
				return $this->$function();
			} else if (property_exists($this, $property)) {
				return $this->$property;
			} else {
				throw new Exception("Could not get property. No such property: " . $property);
			}
		}

		/**
		 * Magic function for setting variables. Will check to see if there
		 * is an override (of the form "set" + Variable_name) first.
		 */
		public function __set($property, $value) {
			// check for an override
			$function = 'set' . ucfirst($property);
			if (method_exists($this, $function)) {
				$this->$function($value);
			} else if (property_exists($this, $property)) {
				$this->$property = $value;
			} else {
				throw new Exception("Could not set property. No such property: " . $property);
			}
		}

		public function startBlock($blockName) {
			ob_start();
		}

		public function endBlock($blockName) {
			$GLOBALS['html'][$blockName] = ob_get_contents();
			ob_end_clean();
		}



		public function addLinkTag($rel, $type, $href) {
			$this->linkTags[] = array('rel' => $rel, 'type' => $type, 'href' => $href);
		}

		public function addMetaTag($name = "", $httpEquiv = "", $content, $overwrite = true) {
			if (!empty($name)) {
				if (!$this->hasMetaTag($name) || $overwrite)
					$this->metaTags[$name] = array('http-equiv' => $httpEquiv, 'content' => $content);
			} else {
				$this->metaTags[] = array('http-equiv' => $httpEquiv, 'content' => $content);
			}
		}

		public function hasMetaTag($name = "") {
			return array_key_exists($name, $this->metaTags);
		}

		public function addStylesheet($href, $mediaType = "screen", $retain = false) {
			$this->stylesheets[] = array('href' => $href, 'media' => $mediaType, 'retain' => $retain);
		}

		public function addInternalStylesheet($styles, $mediaType = "screen") {
			$this->internalStylesheets[] = array('styles' => $styles, 'media' => $mediaType);
		}

		public function clearStylesheets() {
			$stylesheets = array();
			foreach ($this->stylesheets as $stylesheet) {
				if (array_key_exists('retain', $stylesheet) && $stylesheet['retain'] == true)
					$stylesheets[] = $stylesheet;
			}
			$this->stylesheets = $stylesheets;
			$this->internalStylesheets = array();
		}

		public function clearScripts() {
			$scripts = array();
			foreach ($this->scripts as $script) {
				if (array_key_exists('retain', $script) && $script['retain'] == true)
					$scripts[] = $script;
			}
			$this->scripts = $scripts;
		}

		public function addScript($src, $prepend = false) {
			if ($prepend)
				array_unshift($this->scripts, array('src' => $src));
			else
				$this->scripts[]['src'] = $src;
		}

		public function addHeadScript($script) {
			$this->headScripts[] = $script;
		}

		public function addBodyStartScriptTag($script) {
			$this->bodyStartScriptTags[] = $script;
		}

		public function addBodyEndScriptTag($script) {
			$this->bodyEndScriptTags[] = $script;
		}

		public function removeTemplate() {
			unset($this->pageTemplatePath);
		}

		public function loadData($data) {
			$this->data = $data;
			//echo("data: " . $this->data);
			//die();
			if(file_exists($this->data)){
				$this->xml = simplexml_load_file($this->data);
			} else {
				throw new Exception('XML file not found: ' . $this->data);
			}
		}

		public static function getBlock($block) {

			return "<!-- Start of block {$block} -->\n" . @$GLOBALS['html'][$block] . "\n<!-- End of block {$block} -->\n";

		}

		public function render() {

			if (ob_get_length() > 0)
				ob_clean();

			$pageTemplate = "";
			if (isset($this->pageTemplatePath) && !empty($this->pageTemplatePath)) {
				ob_start();
				require_once($this->pageTemplatePath);
				$pageTemplate = ob_get_clean();
			}

			$tagEnd = ' />';

			echo '<!doctype html>' . "\n";
			echo '<html>' . "\n";
			echo '<head>' . "\n";
			echo '<title>' . $this->title . '</title>' . "\n";

			foreach ($this->metaTags as $name => $metaTag) {
				if (is_string($name))
					echo '<meta name="' . $name . '" content="' . $metaTag['content'] . '"' . $tagEnd . "\n";
				else
					echo '<meta http-equiv="' . $metaTag['http-equiv'] . '" content="' . $metaTag['content'] . '"' . $tagEnd . "\n";
			}
			foreach ($this->linkTags as $linkTag) {
				echo '<link rel="' . $linkTag['rel'] . '" type="' . $linkTag['type'] . '" href="' .
					$linkTag['href'] . '"' . $tagEnd . "\n";
			}
			foreach ($this->stylesheets as $stylesheet) {
				echo '<link rel="stylesheet" type="text/css" href="' .
					$stylesheet['href'] . '" media="' . $stylesheet['media'] . '"' . $tagEnd . "\n";
			}
			foreach ($this->internalStylesheets as $style) {
				echo '<style type="text/css" media="' . $style['media'] . '">' . "\n";
				echo $style['styles'] . "\n";
				echo '</style>' . "\n";
			}
			foreach ($this->scripts as $script) {
				echo '<script type="text/javascript" src="' . $script['src'] . '"></script>' . "\n";
			}
			foreach ($this->headScripts as $script) {
				echo '<script type="text/javascript">' . $script . '</script>' . "\n";
			}
			echo '</head>' . "\n";
			echo '<body' . (isset($this->bodyID) ? ' id="' . $this->bodyID . '"' : '') . (isset($this->bodyClass) ? ' class="' . $this->bodyClass . '"' : '') . '>' . "\n";
			foreach ($this->bodyStartScriptTags as $bodyStartScriptTag) {
				echo $bodyStartScriptTag . "\n";
			}
			echo $pageTemplate;
			foreach ($this->bodyEndScriptTags as $bodyEndScriptTag) {
				echo $bodyEndScriptTag . "\n";
			}
			if(!DEBUG && $GLOBALS['site']->getAttributes()->contains('GOOGLEANALYTICID') && substr($_SERVER['REQUEST_URI'], 0, strlen(SITE_PATH . "admin/")) != SITE_PATH . "admin/") {
				$googleAnalyticsID = $GLOBALS['community']->getAttributes()->get('GOOGLEANALYTICID')->getValue();
				echo <<<EOANALYTICS
				<script type="text/javascript">
					var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
					document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
				</script>
				<script type="text/javascript">
					try {
						var pageTracker = _gat._getTracker("{$googleAnalyticsID}");
						pageTracker._trackPageview();
					} catch(err) {}
				</script>
EOANALYTICS;
			}
			echo "\n" . '</body>' . "\n";
			echo '</html>';
		}
	}

?>