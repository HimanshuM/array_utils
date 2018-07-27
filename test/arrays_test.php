<?php

namespace ArrayUtils\Test;

use Phpm\UnitTest;
use ArrayUtils\Arrays;

	class ArraysTest extends UnitTest {

		protected $arr;

		function setUp() {
			$this->arr = Arrays::range(1, 100);
		}

		function testIndexed() {
			$this->assertEquals(50, $this->arr[49]);
		}

		function testNamed() {
			$this->assertEquals(11, $this->arr->eleventh);
		}

		function testLast() {
			$this->assertEquals(100, $this->arr->last);
		}

		function testLength() {
			$this->assertEquals(100, $this->arr->length);
		}

		function testLengthAsMethod() {
			$this->assertEquals(100, $this->arr->length());
		}

	}

?>