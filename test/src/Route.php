<?php
namespace Lucinda\MVC\STDOUT;

class Route {
	private $path;

	public function __construct($path) {
		$this->path = $path;
	}

	public function getPath() {
		return $this->path;
	}
}