<?php

namespace Pangolia\ContainerTests\Mocks\Modules\ModuleA;

use Pangolia\ContainerTests\Mocks\Abstracts\TestAbstractA;
use Pangolia\ContainerTests\Mocks\Interfaces\TestInterfaceA;

class TestModuleAComponentA extends TestAbstractA implements TestInterfaceA {
	public function __construct() {
		$GLOBALS['container'][get_called_class()]['instances']++;
		$GLOBALS['container'][get_called_class()]['instantiated'] = true;
	}
	public function register() {
		$GLOBALS['container'][get_called_class()]['registered'] = true;
	}

	public function testAbstractFunction(): bool {
		return true;
	}

	public function interfaceAFunction(): string {
		return 'true';
	}
}