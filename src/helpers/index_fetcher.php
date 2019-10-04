<?php

namespace ArrayUtils\Helpers;

use StringHelpers\Str;

	trait IndexFetcher {

		protected function fetchByIndex($index, $value = nil) {

			if ($index == "last") {
				return $this->last($value);
			}

			$index = self::getIndex($index) - 1;
			if ($index === false) {
				throw new Exception("Invalid index '$index'", 1);
			}
			if ($index == -1) {
				return $this->last($value);
			}
			else {

				if (empty($this->_internal)) {
					return false;
				}

				if (!in_array($index, $this->_keys)) {
					return false;
				}

				if ($value === nil) {
					return $this->_internal[$index];
				}
				else {
					$this->_internal[$index] = $value;
				}

			}

		}

		static function getIndex($rank) {

			if (in_array($rank, self::Ones)) {
				return array_search($rank, self::Ones) + 1;
			}
			else if (isset(self::Tens[$rank])) {

				if (is_numeric(self::Tens[$rank])) {
					return self::Tens[$rank];
				}
				else {

					$index = array_search($rank, array_keys(self::Tens));
					return 10 * ($index - 8);

				}

			}

			$rank = Str::snakeCase($rank);
			$multiples = explode("_", $rank);

			if (count($multiples) == 2) {

				if (in_array($multiples[0], self::Tens) && in_array(lcfirst($multiples[1]), self::Ones)) {

					$index = array_search($multiples[0], array_values(self::Tens));
					return (10 * ($index - 8)) + (array_search($multiples[1], self::Ones) + 1);

				}

			}
			else if (count($multiples) == 3) {

			}
			else {
				return -1;
			}

			return false;

		}

	}

?>