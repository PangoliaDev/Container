<?php

namespace Pangolia\ContainerTests\Mocks\Interfaces;

use Pangolia\Container\Registrable;

interface TestInterfaceA extends Registrable {
	public function interfaceAFunction(): string;
}