<?php

namespace Pangolia\ContainerTests\Mocks;

use Pangolia\ContainerTests\Mocks\Interfaces\TestInterfaceA;

class TestComponentB implements TestInterfaceA {
	public TestExternalA $testExternalA;

	public function __construct(
		TestExternalA $testExternalA
	) {
		$GLOBALS['container'][ get_called_class() ]['instances']++;
		$GLOBALS['container'][ get_called_class() ]['instantiated'] = true;

		$this->testExternalA = $testExternalA;
	}

	public function register() {
		$GLOBALS['container'][ get_called_class() ]['registered'] = true;
	}

	public function interfaceAFunction(): string {
		return 'true';
	}
}