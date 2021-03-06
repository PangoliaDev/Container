<?php
declare( strict_types = 1 );

namespace Pangolia\Container;

abstract class Autowiring {

	/**
	 * Project's classes found from PSR4Prefixes.
	 *
	 * @since 0.1.0
	 * @var string[]|array<string, mixed>
	 */
	protected $psr4_classes = [];

	/**
	 * Project namespace.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $namespace;

	/**
	 * Prepare the autowired services, this will get an array with services based on the
	 * namespace hierarchies that we define in the services() method & composer's autoloader
	 *
	 * @param array<string, mixed> $psr4_prefixes
	 * @param array<int, string>   $services
	 * @return array<int, string>
	 * @since 0.1.0
	 */
	protected function prepare_autowired_services( array $psr4_prefixes, array $services ): array {
		// Will get all the project classes from the project dir using the
		// namespace and Composer's autoloader
		$this->find_psr4_classes(
			$psr4_prefixes[ $this->namespace . '\\' ][0] ?? ''
		);

		// Prepare autowired services array
		$autowired_services = [];

		foreach ( $services as $service ) {

			// We're going to get all the project's relevant classes and match them with the service hierarchies
			// that we define in the services() method, so we only instantiate what we want
			foreach ( $this->find_relevant_classes() as $project_class ) {
				if ( \substr( $project_class, 0, \strlen( $service ) ) === $service ) {
					$autowired_services[] = $project_class;
				}
			}
		}
		return $autowired_services;
	}

	/**
	 * Collects the classes from a namespace path.
	 *
	 * @param string $path
	 * @return void
	 * @since 0.1.0
	 */
	private function find_psr4_classes( string $path ) {
		if ( \is_dir( $path ) ) {
			$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $path ) );
			foreach ( $files as $file ) {
				if ( $file->isDir() ) {
					continue;
				}
				if ( \preg_match( '/[A-Z].*.php/', $file->getFileName() ) ) {
					$sub_namespace_path = \str_replace(
						[ $path, DIRECTORY_SEPARATOR, '.php' ],
						[ '', '\\', '' ],
						$file->getPathname()
					);
					// eg: TheProjectName/Class/Class
					$this->psr4_classes[] = $this->namespace . $sub_namespace_path;
				}
			}
		}
	}

	/**
	 * Get all the relevant classes from the PSR4 classmap.
	 *
	 * @return string[]
	 * @since 0.1.0
	 */
	private function find_relevant_classes(): array {
		$relevant_classes = [];

		foreach ( $this->psr4_classes as $class ) {
			if ( strpos( $class, '\\Tests\\' ) !== false ) {
				continue; // Skip test classes
			}

			if ( \class_exists( $class ) ) {
				$refl_class = new \ReflectionClass( $class );
				if (
					$class === get_called_class()
					|| $refl_class->isAbstract()
					|| $refl_class->isInterface()
					|| $refl_class->isTrait()
				) {
					continue; // Skip abstract classes, interfaces, traits & the container class itself.
				}

				// todo find classes by injected interface
				//
				//				if ( ! empty( $refl_class->getConstructor() ) && ! empty( $refl_class->getConstructor()->getParameters() ) ) {
				//					foreach ( $refl_class->getConstructor()->getParameters() as $refl_param ) {
				//						$refl_class_for_param = $refl_param->getClass();
				//						if ( $refl_class_for_param->isInterface() ) {
				//							fwrite( STDERR, print_r([
				//								'name' => $refl_class->getName(),
				//								'param_name' =>  $refl_param->getName(),
				//								'param_class' =>  $refl_class_for_param
				//							], true) );
				//						}
				//					}
				//				}

				$relevant_classes[] = $class;
			}
		}

		return $relevant_classes;
	}
}
