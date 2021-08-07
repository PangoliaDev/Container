<?php
declare( strict_types = 1 );

namespace Pangolia\Container;

/**
 * Auto wiring should only be done in dev environment or only once prior to being cached.
 */
abstract class Autowiring {

	/**
	 * Project's classes found from PSR4Prefixes.
	 *
	 * @var string[]|array<string, mixed>
	 */
	protected $psr4_classes = [];

	/**
	 * Dice rules.
	 *
	 * @var array<string, mixed>
	 */
	protected $rules = [];

	/**
	 * Project namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * Prepare the autowired services, this will get an array with services based on the
	 * namespace hierarchies that we define in the services() method & composer's autoloader
	 *
	 * @param $psr4Prefixes
	 * @param $services
	 * @return array
	 */
	protected function prepare_autowired_services( $psr4Prefixes, $services ): array {
		// Will get all the project classes from the project dir using the
		// namespace and Composer's autoloader
		$this->find_psr4_classes(
			$psr4Prefixes[ $this->namespace . '\\' ][0] ?? ''
		);

		// Prepare autowired services array
		$autowired_services = [];

		foreach ( $services as $service ) {

			// We're going to get all the project's relevant classes and match them with the service hierarchies
			// that we define in the services() method, so we only instantiate what we want
			foreach ( $this->find_relevant_classes() as $project_class ) {
				if ( \substr( $project_class, 0, \strlen( $service ) ) === $service ) {
					$autowired_services[] = $project_class;

					// Add "shared" rule so the service will only instantiate once,
					// even when its being injected
					$this->rules[ $project_class ]['shared'] = true;
				}
			}
		}
		return $autowired_services;
	}

	/**
	 * Collects the classes from a namespace path.
	 *
	 * @return void
	 */
	private function find_psr4_classes( $path ) {
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
	 */
	private function find_relevant_classes(): array {
		$relevant_classes = [];

		foreach ( $this->psr4_classes as $class ) {
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

				$relevant_classes[] = $class;
			}
		}
		return $relevant_classes;
	}
}
