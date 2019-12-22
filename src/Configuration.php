<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Validator\SQL\DataSource;
use Lucinda\UnitTest\Validator\SQL;

/**
 * Detects unit tests configuration based on XML file and development environment
 */
class Configuration
{
    private $simpleXMLElement;
    
    /**
     * Reads unit tests configuration based on XML file and development environment
     * 
     * @param string $xmlFilePath
     * @param string $developmentEnvironment
     * @throws Exception
     */
    public function __construct(string $xmlFilePath, string $developmentEnvironment)
    {
        if (!file_exists($xmlFilePath)) {
            throw new Exception("XML file not found: ".$xmlFilePath);
        }
        $this->simpleXMLElement = simplexml_load_file($xmlFilePath);
        
        $this->setSQLDataSource($developmentEnvironment);
    }
    
    /**
     * Detects SQL data source based on servers > sql > {ENVIRONMENT} > server tag.
     * 
     * @param string $developmentEnvironment
     * @throws Exception
     */
    private function setSQLDataSource(string $developmentEnvironment): void
    {
        
        $xml = $this->simpleXMLElement->servers->sql->{$developmentEnvironment}->server;
        if (empty($xml)) {
            throw new Exception("Tag servers/sql not configured for: ".$developmentEnvironment);
        }
            
        $dataSource = new DataSource();
        $dataSource->setDriverName((string) $xml["driver"]);
        $dataSource->setDriverOptions(array()); // currently, setting driver options isn't possible
        $dataSource->setHost((string) $xml["host"]);
        $dataSource->setPort((integer) $xml["port"]);
        $dataSource->setUserName((string) $xml["username"]);
        $dataSource->setPassword((string) $xml["password"]);
        $dataSource->setSchema((string) $xml["schema"]);
        $dataSource->setCharset((string) $xml["charset"]);
        SQL::setDataSource($dataSource);
    }
}
