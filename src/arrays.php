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

	class Arrays implements ArrayAccess, Countable, Iterator, JsonSerializable, Serializable {

		use Helpers\Accessor;
		use Helpers\Counter;
		use Helpers\IndexFetcher;
		use Helpers\TIterator;
		use Helpers\JsonSerializer;
		use Helpers\Serializer;

		use Accessor;

		protected $_internal = [];
		protected $_keys = [];
		protected $_position = 0;

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

		protected function copyFrom($array) {

			if (!is_a($array, Arrays::class)) {
				return false;
			}

			$this->_keys = $array->_keys;
			$this->_internal = $array->_internal;
			$this->_position = $array->_position;

		}

		function delete($key) {

			$value = null;
			if (in_array($key, $this->_keys)) {

				$value = $this->_internal[$key];
				unset($this->_internal[$key]);
				$this->_reevaluate();

			}

			return $value;

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
			return in_array($key, $this->_keys, true);
		}

		static function explode($delimiter, $string) {
			return new static(explode($delimiter, $string));
		}

		function fetch($key) {

			if (is_a($key, Arrays::class)) {
				$key = $key->_internal;
			}

			if (is_array($key)) {

				if (!empty($values = array_intersect_key($this->_internal, array_flip($key)))) {
					return new static($values);
				}

			}
			else if (in_array($key, $this->_keys, true)) {

				if (!is_array(($value = $this->_internal[$key]))) {
					return $value;
				}

				return new static($value);

			}

			if (in_array($key, $this->_keys)) {
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

		function filter($callback, $flag = 0) {

			if (is_string($callback) && $callback[0] == ":") {
				return $this->invoke(substr($callback, 1), 1);
			}

			return new Arrays(array_filter($this->_internal, $callback, $flag));

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

		function ignore($keys = []) {

			if (is_a($keys, Arrays::class)) {
				$keys = $keys->_internal;
			}
			else if (!is_array($keys)) {
				$keys = [$keys];
			}

			return new Arrays(array_diff_key($this->_internal, array_flip($keys)));

		}

		function intersect($arr) {

			if (is_a($arr, "ArrayUtils\\Arrays")) {
				$arr = $arr->_internal;
			}
			else if (!is_array($arr)) {
				$arr = [$arr];
			}

			return new Arrays(array_intersect($this->_internal, $arr));

		}

		function invoke($arg, $map = 0) {

			$walkers = ["array_map", "array_filter", "array_walk"];

			if ($map >= count($walkers)) {
				$map = 0;
			}

			$map = $walkers[$map];

			return new Arrays($map(function($e) use ($arg) {

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

			if (is_a($arr, Arrays::class)) {
				$arr = $arr->_internal;
			}

			if (!is_array($arr)) {
				$this->_internal[] = $arr;
			}
			else {
				$this->_internal = array_merge($this->_internal, $arr);
			}

			$this->_reevaluate();

			return $this;

		}

		function pick($keys = []) {

			if (is_a($keys, Arrays::class)) {
				$keys = $keys->_internal;
			}

			return new Arrays(array_intersect_key($this->_internal, array_flip($keys)));

		}

		function pluck() {

			$keys = func_get_args();

			$return = new Arrays;
			foreach ($this->_internal as $each) {
				$return[] = new Arrays(array_values(array_intersect_key($each, array_flip($keys))));
			}

			return $return;

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

		function recursiveMerge($arr = []) {

			if (is_a($arr, Arrays::class)) {
				$arr = $arr->_internal;
			}

			if (!is_array($arr)) {
				$this->_internal[] = $arr;
			}
			else {
				$this->_internal = array_merge_recursive($this->_internal, $arr);
			}

			$this->_reevaluate();

			return $this;

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
			return new static(explode($delimiter, $string));
		}

		function take($length) {
			return $this->firstFew($length);
		}

		function unique($flag = SORT_STRING) {
			return new static(array_unique($this->_internal, $flag));
		}

		function values() {
			return new static(array_values($this->_internal));
		}

		function walk($callback, $userData = null) {

			if (is_string($callback) && $callback[0] == ":") {
				return $this->invoke(substr($callback, 1), 2);
			}

			return new static(array_walk($this->_internal, $callback, $userData));

		}

	}

?>