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

			$this->_keys = array_keys($this->_internal);

		}

		function offsetExists($offset) {
			return in_array($offset, $this->_keys, true);
		}

		function offsetUnset($offset) {

			if (in_array($offset, $this->_keys)) {

				unset($this->_internal[$offset]);
				array_splice($this->_keys, array_search($offset, $this->_keys), 1);

			}

		}

		function offsetGet($offset) {
			return in_array($offset, $this->_keys) ? $this->_internal[$offset] : null;
		}

	}

?>