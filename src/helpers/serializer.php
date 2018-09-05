<?php

namespace ArrayUtils\Helpers;

	trait Serializer {

		/* Serializable functions */
		function serialize() {
			return serialize($this->_internal);
		}

		function unserialize($value) {

			$this->__construct();

			$value = unserialize($value);

			if (!is_array($value)) {
				$value = [$value];
			}

			$this->_internal = $value;
			$this->_reevaluate();

		}

	}

?>