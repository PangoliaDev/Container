<?php
declare( strict_types = 1 );

namespace Pangolia\Container;

abstract class Container extends Autowiring {
	const RULES = 'rules';
	const SERVICES = 'services';
	const CACHE_FOLDER = 'container';

	/**
	 * Services created from the container.
	 *
	 * @since 0.1.0
	 * @var mixed
	 */
	public $services = [];

	/**
	 * Composer PSR4 prefixes.
	 *
	 * @since 0.1.0
	 * @var array<string, array>
	 */
	protected $psr4_prefixes;

	/**
	 * The Dice instance.
	 *
	 * @since 0.1.0
	 * @var \Dice\Dice
	 */
	protected $container;

	/**
	 * The project root path.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $path;

	/**
	 * The project's cache folder
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $cache_folder;

	/**
	 * The project's environment
	 *
	 * @since 0.1.0
	 * @var string (prod|dev)
	 */
	protected $environment;

	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 */
	public function __construct( $config, $psr4_prefixes ) {
		$this->psr4_prefixes = $psr4_prefixes;
		$this->namespace = $config['namespace'] ?? '';
		$this->cache_folder = $config['cache_folder'] ?? 'build';
		$this->environment = $config['environment'] ?? 'dev';
		$this->path = \dirname( $this->psr4_prefixes[ $this->namespace . '\\' ][0] ?? '' );
		$this->container = new \Dice\Dice();
	}

	/**
	 * Register the project
	 *
	 * @since 0.1.0
	 */
	public function register( $register_hook = 'after_setup_theme' ) {
		\add_action( $register_hook, [ $this, 'register_services' ] );
	}

	/**
	 * Register Services
	 *
	 * @since 0.1.0
	 */
	public function register_services() {
		foreach ( $this->get_autowired_services() as $service ) {

			// Create service.
			$this->services[ $service ] = $this->container->create( $service );

			// Register the registrable.
			if ( $this->services[ $service ] instanceof RegisterInterface ) {
				$this->services[ $service ]->register();
			}
		}
	}

	/**
	 * Get the autowired services.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	private function get_autowired_services(): array {
		// Remove cache if we're in dev environment
		if ( $this->environment === 'dev' ) {
			$this->remove_cache();
		}

		// Cache the services
		$autowired_services = $this->get_cache( $this::SERVICES, function () {
			// Set the self-defined rules
			$this->rules = $this->rules();

			// Prepare the autowired services, this will get an array with classes based on the namespace
			// hierarchies that we define in the services() method & the project folder from composer's autoloader
			return $this->prepare_autowired_services( $this->psr4_prefixes, $this->services() );
		} );

		// Cache the rules
		$this->rules = $this->get_cache( $this::RULES, function () {
			return $this->rules;
		} );

		// Add rules to Dice.
		$this->container = $this->container->addRules( $this->rules );

		return $autowired_services;
	}

	/**
	 * Autowired services.
	 *
	 * Services can be namespace hierarchies to point all classes within a folder or
	 * the full namespace of a class.
	 *
	 * The services are being cached, in order to delete the cache set environment to dev or use remove_cache();
	 *
	 * @return string[]|array<string, string>
	 * @since 0.1.0
	 */
	abstract protected function services(): array;

	/**
	 * Container rules to configure the services and injections.
	 *
	 * In order to allow complete flexibility, the container can be fully configured using
	 * rules provided by associative arrays rules.
	 *
	 * @docs https://r.je/dice#example3
	 *
	 * The rules are being cached, in order to delete the cache set environment to dev or use remove_cache();
	 *
	 * @return array<string, mixed>
	 * @since 0.1.0
	 */
	protected function rules(): array {
		return [];
	}

	/*
	|--------------------------------------------------------------------------
	| Cache methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Returns the path where our cached files will be saved.
	 *
	 * @return string
	 * @since 0.1.0
	 */
	private function get_cache_path(): string {
		return \trailingslashit( $this->path ) . \trailingslashit( $this->cache_folder ) . $this::CACHE_FOLDER;
	}

	/**
	 * Get the cached data.
	 *
	 * @param string               $cache_key The cache key / file name.
	 * @param callable|mixed|false $callback  Callback function to save data in case nothing is found.
	 *                                        This can also be the value itself.
	 * @return mixed
	 * @since 0.1.0
	 */
	private function get_cache( string $cache_key, $callback ) {
		$path = $this->get_cache_path();
		$file = \trailingslashit( $path ) . $cache_key . '.php';
		return \is_file( $file )
			? include $file
			: $this->set_cache( $path, $callback, $file );
	}

	/**
	 * Create the cached file.
	 *
	 * @param string               $path     The path to the cache dir.
	 * @param callable|mixed|false $callback Callback function which returns the data to cache,
	 *                                       or the data itself.
	 * @param string               $file     The full file path.
	 * @return mixed
	 * @since 0.1.0
	 */
	private function set_cache( string $path, $callback, string $file ) {
		if ( ! \is_dir( $path ) ) \mkdir( $path );
		\file_put_contents( $file, $this->render_php( $data = \is_callable( $callback )
			? $callback()
			: $callback
		) );
		return $data;
	}

	/**
	 * Remove all cache files
	 *
	 * @since 0.1.0
	 */
	public function remove_cache() {
		$path = $this->get_cache_path();
		if ( \is_dir( $path ) ) {
			$files = \glob( $path . '/*' );
			foreach ( $files as $file ) {
				if ( \is_file( $file ) ) {
					\unlink( $file );
				}
			}
			\rmdir( $path );
		}
	}

	/**
	 *
	 * Render the php code for the cached files.
	 *
	 * @param mixed $data
	 * @return string
	 * @since 0.1.0
	 */
	private function render_php( $data ): string {
		$php = '<?php ' . PHP_EOL;
		$php .= $this->render_php_doc() . PHP_EOL;
		$php .= 'declare( strict_types = 1 ); ' . PHP_EOL . PHP_EOL;
		$php .= 'return ' . \var_export( $data, true ) . ';';
		return $php;
	}

	/**
	 * Render php docs for the cached files.
	 *
	 * @return string
	 * @since 0.1.0
	 */
	private function render_php_doc(): string {
		$php_doc = '/**' . PHP_EOL;
		$php_doc .= ' * This file has been auto-generated by the project in production-mode' . PHP_EOL;
		$php_doc .= ' * and is intended to store data permanently without expiration.';
		$php_doc .= '*/' . PHP_EOL;
		return $php_doc;
	}

	/*
	|--------------------------------------------------------------------------
	| Read methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Finds an entry of the container by its identifier (class namespace) and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return mixed Entry.
	 * @since   0.1.0
	 * @throws NotFoundException
	 *
	 * @example $container->get(ProjectClass::class)
	 */
	public function get( string $id ) {
		if ( $this->has( $id ) === false ) {
			throw new NotFoundException( 'Could not find ' . $id );
		}

		return $this->services[ $id ];
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 * @since   0.1.0
	 *
	 * @example $container->has(ProjectClass::class)
	 */
	public function has( string $id ): bool {
		return isset( $this->services[ $id ] );
	}
}
