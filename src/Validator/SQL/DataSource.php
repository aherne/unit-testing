<?php
namespace Lucinda\UnitTest\Validator\SQL;

/**
 * Encapsulates connection information to an SQL server
 */
class DataSource
{
    private $driverName;
    private $driverOptions;
    private $host;
    private $port;
    private $userName;
    private $password;
    private $schema;
    private $charset;
    
    /**
     * Sets database server driver name.
     *
     * @param string $driverName
     */
    public function setDriverName(string $driverName): void
    {
        $this->driverName = $driverName;
    }
    /**
     * Gets database server vendor.
     *
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->driverName;
    }
    /**
     * Sets database server vendor PDO connection options
     *
     * @param array $driverOptions
     */
    public function setDriverOptions(array $driverOptions): void
    {
        $this->driverOptions = $driverOptions;
    }
    /**
     * Gets database server vendor PDO connection options
     *
     * @return array
     */
    public function getDriverOptions(): array
    {
        return $this->driverOptions;
    }
    /**
     * Sets database server host name
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }
    /**
     * Gets database server host name
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }
    /**
     * Sets database server port
     *
     * @param integer $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }
    /**
     * Gets database server port
     *
     * @return integer
     */
    public function getPort(): int
    {
        return $this->port;
    }
    /**
     * Sets database server user name
     *
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }
    /**
     * Gets database server user name
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }
    /**
     * Sets database server user password
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    /**
     * Gets database server user password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    /**
     * Sets database server default schema
     *
     * @param string $schema
     */
    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }
    /**
     * Gets database server default schema
     *
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }
    /**
     * Sets database server default charset
     *
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }
    /**
     * Gets database server default charset.
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }
}
