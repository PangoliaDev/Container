<?php
declare( strict_types = 1 );

namespace Pangolia\Container;

interface RegisterInterface {

	/**
	 * Register the object with the WordPress system.
	 *
	 * The container will call the register() method in every class that implements this interface,
	 * which holds the actions and filters - effectively replacing the need to manually add
	 * them in one place.
	 *
	 * @return void
	 *
	 * @since 0.1.0
	 */
	public function register();
}
