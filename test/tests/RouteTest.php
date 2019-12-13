<?php
namespace Lucinda\MVC\STDOUT;

class RouteTest {
	public function __constructTest() {
		$output = [];
        $output[] = new \Hlis\Testing\UnitTestResult(false, "mmm");
        $output[] = new \Hlis\Testing\UnitTestResult(true);
        return $output;
	}
}