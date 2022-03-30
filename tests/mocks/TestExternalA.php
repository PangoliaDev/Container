<?php

namespace Pangolia\ContainerTests\Mocks;

class TestExternalA {
	protected string $somethingVariable;

	public function __construct( $somethingVariable ) {
		$GLOBALS['container'][ get_called_class() ]['instances']++;
		$GLOBALS['container'][ get_called_class() ]['instantiated'] = true;
		$this->somethingVariable = $somethingVariable;
	}

	public function testFunction(): string {
		return $this->somethingVariable;
	}
}