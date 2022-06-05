<?php

namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Validator\SQL\DataSource;
use Lucinda\UnitTest\Configuration\UnitTestedAPI;

/**
 * Detects unit tests configuration based on XML file and development environment
 */
class Configuration
{
    private \SimpleXMLElement $simpleXMLElement;
    private ?DataSource $sqlDataSource = null;
    /**
     * @var UnitTestedAPI[]
     */
    private array $apis = [];

    /**
     * Reads unit tests configuration based on XML file and development environment
     *
     * @param  string $xmlFilePath
     * @param  string $developmentEnvironment
     * @throws Exception
     */
    public function __construct(string $xmlFilePath, string $developmentEnvironment)
    {
        if (!file_exists($xmlFilePath)) {
            throw new Exception("XML file not found: ".$xmlFilePath);
        }
        $this->simpleXMLElement = simplexml_load_file($xmlFilePath);

        $this->setAPIs();
        $this->setSQLDataSource($developmentEnvironment);
    }

    /**
     * Detects API under testing by contents of 'unit_tests' tag
     *
     * @throws Exception
     */
    private function setAPIs(): void
    {
        $tmp = (array) $this->simpleXMLElement->unit_tests;
        if (empty($tmp["unit_test"])) {
            throw new Exception("Tag empty or not defined in configuration XML: unit_tests");
        }
        $list = (is_array($tmp["unit_test"]) ? $tmp["unit_test"] : [$tmp["unit_test"]]);
        foreach ($list as $unitTest) {
            $this->apis[] = new UnitTestedAPI($unitTest);
        }
    }

    /**
     * Gets APIs under testing
     *
     * @return UnitTestedAPI[]
     */
    public function getAPIs(): array
    {
        return $this->apis;
    }

    /**
     * Detects SQL data source based contents of 'sql' tag and development environment
     *
     * @param string $developmentEnvironment
     */
    private function setSQLDataSource(string $developmentEnvironment): void
    {
        if (empty($this->simpleXMLElement->servers->sql)) {
            return;
        }
        $xml = $this->simpleXMLElement->servers->sql->{$developmentEnvironment}->server;
        if (empty($xml)) {
            return;
        }

        $dataSource = new DataSource();
        $dataSource->setDriverName((string) $xml["driver"]);
        $dataSource->setDriverOptions([]); // currently, setting driver options isn't possible
        $dataSource->setHost((string) $xml["host"]);
        $dataSource->setPort((int) $xml["port"]);
        $dataSource->setUserName((string) $xml["username"]);
        $dataSource->setPassword((string) $xml["password"]);
        $dataSource->setSchema((string) $xml["schema"]);
        $dataSource->setCharset((string) $xml["charset"]);
        $this->sqlDataSource = $dataSource;
    }

    /**
     * Gets SQL data source detected
     *
     * @return ?DataSource
     */
    public function getSQLDataSource(): ?DataSource
    {
        return $this->sqlDataSource;
    }
}
