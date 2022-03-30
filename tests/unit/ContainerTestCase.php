<?php

namespace Pangolia\ContainerTests\Unit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;
use Pangolia\Container\Container;
use Pangolia\ContainerTests\Mocks\TestContainerMock;

class ContainerTestCase extends TestCase {

	/**
	 * @var string
	 */
	protected string $namespace = 'Pangolia\ContainerTests\Mocks';

	/**
	 * @var Container
	 */
	protected Container $container;

	/**
	 * @var array
	 */
	protected array $psr4_classes = [];

	/**
	 * Setup which calls \WP_Mock setup
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\when( '__' )->returnArg( 1 );
		Monkey\Functions\when( '_e' )->returnArg( 1 );
		Monkey\Functions\when( '_n' )->returnArg( 1 );
	}

	/**
	 * Set up our mocked container
	 */
	public function setUpContainer() {
		$this->getPsr4Classes();
		$this->setInstanceState();
		$this->container = new TestContainerMock(COMPOSER_PREFIXES );
		$this->container->set_namespace($this->namespace);
		$this->container->set_environment('dev');
		$this->container->set_path(PANGOLIA_DIR);
		$this->container->register();
		$this->container->register_services();
	}

	/**
	 * Set initial instance state to test
	 */
	public function setInstanceState() {
		foreach ( $this->psr4_classes as $class ) {
			if ( isset( $GLOBALS['container'][ $class ]['instantiated'] ) !== true ) {
				$GLOBALS['container'][ $class ]['instances'] = 0;
				$GLOBALS['container'][ $class ]['instantiated'] = false;
				$GLOBALS['container'][ $class ]['registered'] = false;
			}
		}
	}

	/**
	 * Set classes from dev composer to test
	 */
	public function getPsr4Classes() {
		$this->psr4_classes = $this->collectClassesFromNamespace( $this->psr4_classes );
	}

	/**
	 * Collect classes from dev composer to test
	 */
	public function collectClassesFromNamespace( array $collector ): array {
		$namespace_formatted = $this->namespace . "\\";
		$namespace_path = COMPOSER_PREFIXES[ $namespace_formatted ][0] ?? '';
		if ( is_dir( $namespace_path ) ) {
			$namespace_files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $namespace_path ) );
			foreach ( $namespace_files as $file ) {
				if ( $file->isDir() ) {
					continue;
				}
				if ( preg_match( '/[A-Z].*.php/', $file->getFileName() ) ) {
					$sub_namespace_path = str_replace(
						[ $namespace_path, DIRECTORY_SEPARATOR, '.php' ],
						[ '', '\\', '' ],
						$file->getPathname()
					);
					// eg: TheProjectName/Class/Class
					$collector[] = $this->namespace . $sub_namespace_path;
				}
			}
		}
		return $collector;
	}

	/**
	 * Teardown which calls \WP_Mock tearDown
	 */
	public function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}