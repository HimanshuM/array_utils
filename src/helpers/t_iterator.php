<?php

namespace ArrayUtils\Helpers;

	trait TIterator {

		public function rewind() {
			$this->_position = 0;
		}

		public function current() {

			if (is_array($this->_internal[$this->_keys[$this->_position]])) {
				$this->_internal[$this->_keys[$this->_position]] = new static($this->_internal[$this->_keys[$this->_position]]);
			}

			return $this->_internal[$this->_keys[$this->_position]];

		}

		public function key() {
			return $this->_keys[$this->_position];
		}

		public function next() {
			++$this->_position;
		}

		public function valid() {
			return isset($this->_keys[$this->_position]);
		}

	}

?>