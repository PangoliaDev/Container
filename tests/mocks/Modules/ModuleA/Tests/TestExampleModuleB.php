<?php

namespace Pangolia\ContainerTests\Mocks\Modules\ModuleA\Tests;

class TestExampleModuleB {
	public function __construct() {
		$GLOBALS['container'][get_called_class()]['instances']++;
		$GLOBALS['container'][get_called_class()]['instantiated'] = true;
	}
	public function register() {
		$GLOBALS['container'][get_called_class()]['registered'] = true;
	}
}