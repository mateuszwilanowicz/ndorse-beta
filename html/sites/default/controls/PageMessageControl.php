<?php
	class PageMessageControl {

		public static function messages() {

			if(isset($_REQUEST['m'])) {
				$_SESSION['page_messages'][] = $_REQUEST['m'];
			}

			if(!isset($_SESSION['page_messages'])) {
				return '';
			}

			$out = '';
			foreach($_SESSION['page_messages'] as $msg) {
				$out .= '<div class="page-message">' . $msg . '</div>';
			}
			$_SESSION['page_messages'] = array();

			return $out;

		}

		public static function errors() {

			if(isset($_REQUEST['e'])) {
				$_SESSION['page_errors'][] = $_REQUEST['e'];
			}

			if(!isset($_SESSION['page_errors'])) {
				return '';
			}

			$out = '';
			foreach($_SESSION['page_errors'] as $msg) {
				$out .= '<div class="page-message page-error">' . $msg . '</div>';
			}
			$_SESSION['page_errors'] = array();

			return $out;

		}

		public static function ticker() {



		}

	}
?>