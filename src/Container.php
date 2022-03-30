<?php
declare( strict_types = 1 );

namespace Pangolia\Container;

abstract class Container extends Autowiring {
	const RULES = 'rules';
	const SERVICES = 'services';
	const CACHE_FOLDER = 'container';
	const PROD = 'prod';
	const DEV = 'dev';
	const BUILD = 'build';

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
	 * @param array<string, mixed> $psr4_prefixes
	 * @param array<string, mixed> $config
	 *
	 * @since 0.1.0
	 */
	public function __construct( array $psr4_prefixes ) {
		$this->psr4_prefixes = $psr4_prefixes;
		$this->container = new \Dice\Dice();
	}

	/**
	 * Set the namespace
	 *
	 * @param string $namespace
	 * @return $this
	 * @since 0.4.0
	 */
	public function set_namespace( string $namespace ): self {
		$this->namespace = $namespace ?? '';
		$this->path = $this->path ?? \dirname( $this->psr4_prefixes[ $this->namespace . '\\' ][0] ?? '' );
		return $this;
	}

	/**
	 * Set the environment
	 *
	 * @param string $env (prod|dev)
	 * @return $this
	 * @since 0.4.0
	 */
	public function set_environment( string $env ): self {
		$this->environment = $env ?? static::DEV;
		return $this;
	}

	/**
	 * Set the cache folder (optional)
	 *
	 * @param string $cache_folder
	 * @return $this
	 * @since 0.4.0
	 */
	public function set_cache_folder( string $cache_folder ): self {
		$this->cache_folder = $cache_folder ?? static::BUILD;
		return $this;
	}

	/**
	 * Override composer's path (optional)
	 *
	 * @param string $path
	 * @return $this
	 * @since 0.4.0
	 */
	public function set_path( string $path ): self {
		$this->path = $path;
		return $this;
	}

	/**
	 * Register the project
	 *
	 * @param string $register_hook
	 * @return void
	 * @since 0.1.0
	 */
	public function register( string $register_hook = 'after_setup_theme' ) {
		\add_action( $register_hook, [ $this, 'register_services' ] );
	}

	/**
	 * Register Services
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function register_services() {
		foreach ( $this->get_services() as $service ) {

			// Create service.
			$this->services[ $service ] = $this->container->create( $service );

			// Register the registrable.
			if ( $this->services[ $service ] instanceof Registrable ) {
				$this->services[ $service ]->register();
			}
		}
	}

	/**
	 * Get the autowired services.
	 *
	 * @return array<int, string>
	 * @since 0.2.0
	 */
	private function get_services(): array {
		if ( $this->environment === static::DEV ) {
			$this->remove_cache();
		}

		$services = $this->get_cache( $this::SERVICES, function () {
			// Prepare the autowired services, this will get an array with classes based on the namespace
			// hierarchies that we define in the services() method & the project folder from composer's autoloader
			return $this->prepare_autowired_services( $this->psr4_prefixes, $this->services() );
		} );

		// Add rules to Dice.
		$this->container = $this->container->addRules( $this->resolve_rules( $services ) );

		return $services;
	}

	/**
	 * Resolve and get the rules
	 *
	 * @param array<int, string> $services
	 * @return array<string, mixed>
	 * @since 0.2.0
	 */
	private function resolve_rules( array $services ): array {
		return array_replace_recursive(
			array_fill_keys(
				$services,
				// Add "shared" rule so the service will only instantiate once, even when its being injected,
				// this can get overwritten by the shared value set in the rules() method
				[ 'shared' => true ]
			), $this->rules()
		);
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
		return \trailingslashit( $this->path ) . \trailingslashit( $this->cache_folder ) . static::CACHE_FOLDER;
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
		if ( ! \is_dir( $path ) ) {
			\mkdir( $path, 0777, true );
		}

		$data = \is_callable( $callback )
			? \call_user_func( $callback )
			: $callback;

		\file_put_contents( $file, $this->render_php( $data ) );
		\chmod( $file, 0777 );

		return $data;
	}

	/**
	 * Remove all cache files
	 *
	 * @return array<string, bool>
	 * @since 0.1.0
	 */
	public function remove_cache(): array {
		$path = $this->get_cache_path();
		$deleted = [];

		if ( \is_dir( $path ) ) {
			foreach ( $this->get_path_names( $path . '/*' ) as $file ) {
				if ( \is_file( $file ) ) {
					$deleted[ $file ] = \unlink( $file );
				}
			}

			$deleted[ $path ] = \rmdir( $path );
		}

		return $deleted;
	}

	/**
	 * Find path names matching a pattern
	 *
	 * @param string $pattern
	 * @return array<int,string>
	 */
	private function get_path_names( string $pattern ): array {
		$path_names = \glob( $pattern );

		return $path_names !== false
			? $path_names
			: [];
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
