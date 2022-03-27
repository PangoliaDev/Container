<?php

namespace Pangolia\ContainerTests\Unit;

use Pangolia\ContainerTests\Mocks;

/**
 * Start tests with command "vendor/bin/phpunit" or use "run" by PHPStorm
 */
class ContainerTest extends ContainerTestCase {

	public function testIfContainerIsHooked() {
		$this->setUpContainer();
		self::assertTrue( has_action( 'after_setup_theme', 'Pangolia\ContainerTests\Mocks\TestContainerMock->register_services()' ) );
	}

	public function testInstanceState() {
		$this->setUpContainer();
		$this->assertTrue( $GLOBALS['container'][ Mocks\TestComponentA::class ]['instantiated'] );
		$this->assertTrue( $GLOBALS['container'][ Mocks\TestComponentB::class ]['instantiated'] );
		$this->assertTrue( $GLOBALS['container'][ Mocks\TestComponentC::class ]['instantiated'] );
	}

	public function testRegisteredState() {
		$this->setUpContainer();
		$this->assertTrue( $GLOBALS['container'][ Mocks\TestComponentA::class ]['registered'] );
		$this->assertTrue( $GLOBALS['container'][ Mocks\TestComponentB::class ]['registered'] );
		$this->assertFalse( $GLOBALS['container'][ Mocks\TestComponentC::class ]['registered'] );
	}

	public function testTotalInstances() {
		$this->setUpContainer();
		$this->assertEquals( 1, $GLOBALS['container'][ Mocks\TestComponentA::class ]['instances'] );
		$this->assertEquals( 1, $GLOBALS['container'][ Mocks\TestComponentB::class ]['instances'] );
		$this->assertEquals( 1, $GLOBALS['container'][ Mocks\TestComponentC::class ]['instances'] );
		$this->assertEquals( 3, $GLOBALS['container'][ Mocks\Modules\ModuleA\TestModuleAComponentA::class ]['instances'] );
		$this->assertEquals( 1, $GLOBALS['container'][ Mocks\Modules\ModuleA\TestModuleAComponentB::class ]['instances'] );
	}

	public function testInstanceOfClasses() {
		$this->setUpContainer();
		$this->assertInstanceOf( Mocks\TestComponentA::class, $this->container->get( Mocks\TestComponentA::class ) );
		$this->assertInstanceOf( Mocks\TestComponentB::class, $this->container->get( Mocks\TestComponentB::class ) );
		$this->assertInstanceOf( Mocks\TestComponentC::class, $this->container->get( Mocks\TestComponentC::class ) );
		$this->assertInstanceOf( Mocks\Modules\ModuleA\TestModuleAComponentA::class, $this->container->get( Mocks\Modules\ModuleA\TestModuleAComponentA::class ) );
	}

	public function testInstanceOfInterfaces() {
		$this->setUpContainer();
		$this->assertInstanceOf( Mocks\Interfaces\TestInterfaceA::class, $this->container->get( Mocks\TestComponentC::class )->testComponentA );
		$this->assertInstanceOf( Mocks\Interfaces\TestInterfaceB::class, $this->container->get( Mocks\TestComponentC::class )->testModuleAComponentB );
	}

	public function testInstanceOfAbstracts() {
		$this->setUpContainer();
		$this->assertInstanceOf( Mocks\Abstracts\TestAbstractA::class, $this->container->get( Mocks\TestComponentC::class )->testComponentA );
		$this->assertInstanceOf( Mocks\Abstracts\TestAbstractA::class, $this->container->get( Mocks\TestComponentC::class )->testModuleAComponentA );
	}

	public function testConstructedParamsFromClass() {
		$this->setUpContainer();
		$this->assertTrue( is_bool( $this->container->get( Mocks\TestComponentA::class )->constructedBool ) );
		$this->assertTrue( $this->container->get( Mocks\TestComponentA::class )->constructedBool );
		$this->assertTrue( is_array( $this->container->get( Mocks\TestComponentA::class )->constructedArray ) );
		$this->assertEquals(
			'constructedValueFromArray',
			$this->container->get( Mocks\TestComponentA::class )->constructedArray['constructedKey']
		);
		$this->assertTrue( is_string( $this->container->get( Mocks\TestComponentA::class )->constructedString ) );
		$this->assertEquals(
			'constructedValueFromString',
			$this->container->get( Mocks\TestComponentA::class )->constructedString
		);
		$this->assertTrue( is_callable( $this->container->get( Mocks\TestComponentA::class )->constructedCallback ) );
		$this->assertEquals(
			'constructedValueFromCallback',
			call_user_func( $this->container->get( Mocks\TestComponentA::class )->constructedCallback )
		);
	}

	public function testExternalClassInsertedThroughRulesWithParams() {
		$this->setUpContainer();
		$this->assertInstanceOf( Mocks\TestExternalA::class, $this->container->get( Mocks\TestComponentB::class )->testExternalA );
		$this->assertEquals( 'somethingValue', $this->container->get( Mocks\TestComponentB::class )->testExternalA->testFunction() );
	}
}