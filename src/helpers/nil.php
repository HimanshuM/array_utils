<?php

namespace ArrayUtils\Helpers;

	class Nil {

		private static $_instance = null;

		private function __construct() {

		}

		static function nil() {

			if (empty(Nil::$_instance)) {
				Nil::$_instance = new Nil;
			}

			return Nil::$_instance;

		}

		function __toString() {
			return "NULL";
		}

	}

?>