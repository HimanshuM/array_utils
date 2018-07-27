<?php

namespace ArrayUtils;

use ArrayAccess;
use Exception;
use Countable;
use Iterator;
use JsonSerializable;
use Serializable;

use AttributeHelper\Accessor;
use ArrayUtils\Helpers\Nil;

if (!defined("nil")) {
	define ("nil", Nil::nil());
}

	final class Arrays implements ArrayAccess, Countable, Iterator, JsonSerializable, Serializable {

		use Helpers\Accessor;
		use Helpers\Counter;
		use Helpers\IndexFetcher;
		use Helpers\TIterator;
		use Helpers\JsonSerializer;
		use Helpers\Serializer;

		use Accessor;

		private $_internal = [];
		private $_keys = [];
		private $_position = 0;

		const Ones = [
			"first",
			"second",
			"third",
			"fourth",
			"fifth",
			"sixth",
			"seventh",
			"eigth",
			"nineth"
		];

		const Tens = [
			"tenth" => 10,
			"eleventh" => 11,
			"twelfth" => 12,
			"thirteenth" => 13,
			"fourteenth" => 14,
			"fifteenth" => 15,
			"sixteenth" => 16,
			"seventeenth" => 17,
			"eighteenth" => 18,
			"nineteenth" => 19,
			"twentieth" => "twenty", /* 10th index */
			"thirtieth" => "thirty",
			"fourtieth" => "fourty",
			"fiftieth" => "fifty",
			"sixtieth" => "sixty",
			"seventieth" => "seventy",
			"eightieth" => "eighty",
			"ninetieth" => "ninety"
		];

		function __construct($arr = []) {

			if (!is_array($arr)) {

				if (is_a($arr, "ArrayUtils\\Arrays")) {
					$this->_internal = $arr->_internal;
				}
				else {
					$arr = [$arr];
				}

			}

			$this->_internal = $arr;
			$this->_reevaluate();

			$this->methodsAsProperties("all", "array", "empty", "first", "join", "keys", "last", "length", "pop", "shift", "skip", "take", "values");
			$this->notFoundResponse(ACCESSOR_NOT_FOUND_CALLBACK, "fetchByIndex");

		}

		/*function __get($attr) {

			if ($attr == "length") {
				return count($this->_internal);
			}
			if ($attr == "empty") {
				return empty($this->_internal);
			}

			return $this->fetchByIndex($attr);

		}*/

		/*function __set($attr, $value) {

			if ($attr == "first") {
				$this->_internal[$this->_keys[0]] = $value;
			}
			else if ($attr == "last") {
				$this->_internal[$this->_keys[count($this->_keys) - 1]] = $value;
			}

		}*/

		function __debugInfo() {
			return $this->_internal;
		}

		/* Self defined functions */
		function all($length = null) {

			if (empty($offset)) {
				return $this->array();
			}

			return array_slice($this->_internal, 0, $length);

		}

		function append($value) {

			$this->_internal[] = $value;
			$this->_reevaluate();
			return $this;

		}

		function array() {
			return $this->_internal;
		}

		function clear() {

			$this->_internal = [];
			$this->_keys = [];

			return $this;

		}

		function delete($key) {

			if (in_array($key, $this->_keys)) {

				unset($this->_internal[$key]);
				$this->_reevaluate();

			}

			return $this;

		}

		function diff($arr) {

			if (is_a($arr, "ArrayUtils\\Arrays")) {
				$arr = $arr->_internal;
			}
			else if (!is_array($arr)) {
				$arr = [$arr];
			}

			return new Arrays(array_diff($this->_internal, $arr));

		}

		function empty() {
			return empty($this->_internal);
		}

		function exists($key) {
			return in_array($key, $this->_keys);
		}

		static function explode($delimiter, $string) {
			return new Arrays(explode($delimiter, $string));
		}

		function fetch($key) {

			if (isset($this->_keys[$key])) {
				return $this->_internal[$key];
			}

			if (func_num_args() == 1) {
				throw new Exception("Invalid index '$key'", 1);
			}
			else {

				$arg = func_get_arg(1);
				if (is_callable($arg)) {
					call_user_func_array($arg, [$key]);
				}
				else if (is_a($arg, "Exception")) {
					throw $arg;
				}
				else {
					return $arg;
				}

			}

		}

		function firstFew($length) {

			if (empty($this->_internal)) {
				return false;
			}

			return new Arrays(array_slice($this->_internal, 0, $length));

		}

		function has($value) {
			return in_array($value, $this->_internal);
		}

		function hasKey($key) {
			return $this->exists($key);
		}

		function implode($delimiter) {
			return implode($delimiter, $this->_internal);
		}

		function indexOf($value) {
			return array_search($value, $this->_internal);
		}

		function invoke($arg) {

			return new Arrays(array_map(function($e) use ($arg) {

				if (method_exists($e, $arg)) {
					return $e->$arg();
				}

				return $e->$arg;

			}, $this->_internal));

		}

		function join($delimiter = "_") {
			return implode($delimiter, $this->_internal);
		}

		function keys() {
			return new Arrays($this->_keys);
		}

		function last($value = nil) {

			if (empty($this->_internal)) {
				return false;
			}

			if ($value == Nil::nil()) {
				return $this->_internal[$this->_keys[count($this->_keys) - 1]];
			}

			$this->_internal[$this->_keys[count($this->_keys) - 1]] = $value;

		}

		function lastFew($offset) {

			if (empty($this->_internal)) {
				return false;
			}

			return new Arrays(array_slice($this->_internal, -$offset));

		}

		function length() {
			return count($this->_internal);
		}

		function map($closure) {

			if (is_string($closure) && $closure[0] == ":") {
				return $this->invoke(substr($closure, 1));
			}

			$args = [$closure, $this->_internal];

			if (func_num_args() > 1) {

				$otherArgs = array_slice(func_get_args(), 1);
				foreach ($otherArgs as $arg) {

					if (is_array($arg)) {
						$args[] = $arg;
					}
					else if (is_a($arg, "Arrays\Arrays")) {
						$args[] = $arg->_internal;
					}
					else {
						$args[] = [$arg];
					}

				}

			}

			return new Arrays(call_user_func_array("array_map", $args));

		}

		function merge($arr = []) {

			if (!is_array($arr)) {
				$this->_internal[] = $arr;
			}
			else {
				$this->_internal = array_merge($this->_internal, $arr);
			}

			$this->_reevaluate();

			return $this;

		}

		function pop() {

			if (empty($this->_internal)) {
				return null;
			}

			array_pop($this->_keys);
			return array_pop($this->_internal);

		}

		function positionOf($value) {

			$position = array_search($value, $this->_internal);
			return $position === false ? false : $position + 1;

		}

		function prepend($value) {

			array_unshift($this->_internal, $value);
			$this->_reevaluate();

			return $this;

		}

		static function range($start, $end, $step = 1) {
			return new Arrays(range($start, $end, $step));
		}

		function _reevaluate() {
			$this->_keys = array_keys($this->_internal);
		}

		function shift() {

			if (empty($this->_internal)) {
				return null;
			}

			$r = array_shift($this->_internal);
			$this->_reevaluate();

			return $r;

		}

		function skip($offset = 0) {

			if (empty($this->_internal)) {
				return null;
			}

			return new Arrays(array_slice($this->_internal, $offset));
		}

		function slice($offset = 0, $length = null, $preserveKey = false) {
			return new Arrays(array_slice($this->_internal, $offset, $length, $preserveKey));
		}

		function splice($offset = 0, $length = null, $replacement = []) {

			if (is_null($length)) {
				$length = count($this->_internal);
			}

			array_splice($this->_internal, $offset, $length, $replacement);
			$this->_reevaluate();

			return $this;

		}

		static function split($delimiter, $string) {
			return new Arrays(explode($delimiter, $string));
		}

		function take($length) {
			return $this->firstFew($length);
		}

		function values() {
			return new Arrays(array_values($this->_internal));
		}

	}

?>