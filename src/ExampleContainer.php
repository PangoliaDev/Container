<?php
declare( strict_types = 1 );

namespace Pangolia\Container;

use Pangolia\Container\Container as BuildContainer;

class ExampleContainer extends BuildContainer {

	/**
	 * Container services.
	 *
	 * @inheritDoc
	 */
	protected function services(): array {
		return [];
	}

	/**
	 * Container rules.
	 *
	 * @inheritDoc
	 */
	protected function rules(): array {
		return [];
	}
}
