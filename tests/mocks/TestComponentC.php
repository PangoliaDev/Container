<?php

namespace Pangolia\ContainerTests\Mocks;

use Pangolia\ContainerTests\Mocks\Modules\ModuleA\TestModuleAComponentA;
use Pangolia\ContainerTests\Mocks\Modules\ModuleA\TestModuleAComponentB;

class TestComponentC {
	public TestComponentA $testComponentA;
	public TestModuleAComponentA $testModuleAComponentA;
	public TestModuleAComponentB $testModuleAComponentB;

	public function __construct(
		TestComponentA $testComponentA,
		TestModuleAComponentA $testModuleAComponentA,
		TestModuleAComponentB $testModuleAComponentB
	) {
		$GLOBALS['container'][ get_called_class() ]['instances']++;
		$GLOBALS['container'][ get_called_class() ]['instantiated'] = true;
		$this->testComponentA = $testComponentA;
		$this->testModuleAComponentA = $testModuleAComponentA;
		$this->testModuleAComponentB = $testModuleAComponentB;
	}

	public function register() {
		$GLOBALS['container'][ get_called_class() ]['registered'] = true;
	}

	public function testInjectedClassByInterface(): string {
	 return $this->testComponentA->interfaceAFunction();
	}

	public function testInjectedClassByOrigin(): bool {
		return $this->testComponentA->testAbstractFunction();
	}
}