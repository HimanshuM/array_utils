<?php

namespace ArrayUtils\Helpers;

	/* Countable functions */
	trait Counter {

		function count() {
			return count($this->_internal);
		}

	}

?>