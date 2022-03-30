<?php

namespace Pangolia\ContainerTests\Mocks;

use Pangolia\Container\Container;
use Pangolia\ContainerTests\Mocks\Modules;

class TestContainerMock extends Container {

	protected function services(): array {
		return [
			TestComponentA::class,
			TestComponentB::class,
			TestComponentC::class,

			'Pangolia\ContainerTests\Mocks\Modules',
		];
	}

	protected function rules(): array {
		return [
			Modules\ModuleA\TestModuleAComponentA::class => [
				'shared' => false,
			],

			TestComponentA::class => [
				'constructParams' => [
					'constructedArray'    => [
						'constructedKey' => 'constructedValueFromArray',
					],
					'constructedString'   => 'constructedValueFromString',
					'constructedBool'     => true,
					'constructedCallback' => function () {
						return 'constructedValueFromCallback';
					},
				],
			],

			TestExternalA::class => [
				'constructParams' => [
					'somethingVariable' => 'somethingValue'
				],
			],
		];
	}
}