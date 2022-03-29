<?php

namespace Pangolia\ContainerTests\Mocks\Modules\ModuleA\Tests;

class TestExampleModuleA {
	public function __construct() {
		$GLOBALS['container'][get_called_class()]['instances']++;
		$GLOBALS['container'][get_called_class()]['instantiated'] = true;
	}
	public function register() {
		$GLOBALS['container'][get_called_class()]['registered'] = true;
	}
}