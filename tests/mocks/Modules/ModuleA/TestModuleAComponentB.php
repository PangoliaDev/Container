<?php

namespace Pangolia\ContainerTests\Mocks\Modules\ModuleA;

use Pangolia\ContainerTests\Mocks\Interfaces\TestInterfaceB;

class TestModuleAComponentB implements TestInterfaceB {
	protected TestModuleAComponentA $testModuleAComponentA;
	public function __construct(
		TestModuleAComponentA $testModuleAComponentA
	) {
		$GLOBALS['container'][get_called_class()]['instances']++;
		$GLOBALS['container'][get_called_class()]['instantiated'] = true;
		$this->testModuleAComponentA = $testModuleAComponentA;
	}
	public function register() {
		$GLOBALS['container'][get_called_class()]['registered'] = true;
		$testBoolVariable = $this->testModuleAComponentA->testAbstractFunction();
	}
}