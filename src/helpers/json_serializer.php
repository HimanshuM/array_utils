<?php

namespace ArrayUtils\Helpers;

	trait JsonSerializer {

		function jsonSerialize() {
			return $this->_internal;
		}

	}

?>