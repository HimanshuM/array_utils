<?php

namespace ArrayUtils\Helpers;

	/* ArrayAccess functions */
	trait Accessor {

		function offsetSet($offset, $value) {

			if (is_null($offset)) {
				$this->_internal[] = $value;
			}
			else {
				$this->_internal[$offset] = $value;
			}

			$this->_list = array_keys($this->_internal);
			$this->_keys = array_flip($this->_list);

		}

		function offsetExists($offset) {
			return isset($this->_keys[$offset]);
		}

		function offsetUnset($offset) {

			if (isset($this->_keys[$offset])) {

				unset($this->_internal[$offset]);
				array_splice($this->_list, array_search($offset, $this->_list), 1);
				$this->_keys = array_flip($this->_list);

			}

		}

		function offsetGet($offset) {
			return isset($this->_keys[$offset]) ? $this->_internal[$offset] : null;
		}

	}

?>