<?php

namespace Pangolia\ContainerTests\Mocks;

use Pangolia\ContainerTests\Mocks\Abstracts\TestAbstractA;
use Pangolia\ContainerTests\Mocks\Interfaces\TestInterfaceA;

class TestComponentA extends TestAbstractA implements TestInterfaceA {
	public array $constructedArray;
	public string $constructedString;
	public bool $constructedBool;
	public $constructedCallback;

	public function __construct(
		$constructedArray,
		$constructedString,
		$constructedBool,
		$constructedCallback
	) {
		$GLOBALS['container'][ get_called_class() ]['instances']++;
		$GLOBALS['container'][ get_called_class() ]['instantiated'] = true;

		$this->constructedArray = $constructedArray;
		$this->constructedString = $constructedString;
		$this->constructedBool = $constructedBool;
		$this->constructedCallback = $constructedCallback;
	}

	public function register() {
		$GLOBALS['container'][ get_called_class() ]['registered'] = true;
	}

	public function testAbstractFunction(): bool {
		return true;
	}

	public function interfaceAFunction(): string {
		return 'true';
	}
}