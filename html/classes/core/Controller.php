<?php
	abstract class Controller {

		protected $page;

		private $title;
		private $template;

		public function __construct($title = "", $template = "") {

			$this->title = $title;
			$this->template = $template;

			$this->page = new Page($this->title, $this->template);

			if (isset($GLOBALS['page_settings']['stylesheets']) && is_array($GLOBALS['page_settings']['stylesheets'])) {
				foreach ($GLOBALS['page_settings']['stylesheets'] as $stylesheet)
					$this->page->addStylesheet($stylesheet);
			}

			if (isset($GLOBALS['page_settings']['body_id']))
				$this->page->setBodyId($GLOBALS['page_settings']['body_id']);

			if (isset($GLOBALS['page_settings']['scripts']) && is_array($GLOBALS['page_settings']['scripts'])) {
				foreach ($GLOBALS['page_settings']['scripts'] as $script)
					$this->page->addScript($script, true);
			}
			if (isset($GLOBALS['page_settings']['analytics']))
				$this->page->addBodyEndScriptTag($GLOBALS['page_settings']['analytics']);

			$GLOBALS['current_page'] = $this->page;
		}

		public abstract function index($args);

		protected function render() {

			$this->page->render();

		}

		public function error404($args) {
			$this->page->startBlock('main');
			require_once(localise('layouts/404.php', true));
			$this->page->endBlock('main');
			$this->page->render();

		}

		public function loggedIn($redirect = true) {

			if(isset($_SESSION['user']) && $_SESSION['user']->getID()) {
				return true;
			}

			if($redirect) {
				redirect(BASE_URL);
			}
			return false;

		}

	}

?>
