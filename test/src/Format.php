<?php
namespace Lucinda\MVC\STDOUT;

class Format {
	private $name;

	public function __construct($name, $contentType, $characterEncoding="", $viewResolverClass="") {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}
}