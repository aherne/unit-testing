<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Exception;
use \Lucinda\UnitTest\Result;
use \Lucinda\UnitTest\Validator\SQL\ResultValidator;
use Lucinda\UnitTest\Validator\SQL\DataSource;

/**
 * Defines a UNIT TEST pattern for SQL testing.
 */
class SQL
{
    private static DataSource $dataSource;
    private static SQL $instance;
    
    private \PDO $PDO;
    
    /**
     * Sets data source to be used by SQL instances later on
     *
     * @param DataSource $dataSource
     */
    public static function setDataSource(DataSource $dataSource)
    {
        self::$dataSource = $dataSource;
    }
    
    /**
     * Singleton opening a single connection
     *
     * @throws Exception
     * @return SQL
     */
    public static function getInstance(): SQL
    {
        if (!self::$dataSource) {
            throw new Exception("Data source not configured!");
        }
        if (!self::$instance) {
            self::$instance = new SQL();
        }
        return self::$instance;
    }
    
    
    /**
     * Connects to database based on information in DataSource using PDO then starts a transaction
     *
     * @throws Exception
     */
    private function __construct()
    {
        $dataSource = self::$dataSource;
        $settings = ":host=".$dataSource->getHost();
        if ($dataSource->getPort()) {
            $settings .= ";port=".$dataSource->getPort();
        }
        if ($dataSource->getSchema()) {
            $settings .= ";dbname=".$dataSource->getSchema();
        }
        if ($dataSource->getCharset()) {
            $settings .= ";charset=".$dataSource->getCharset();
        }
        $this->PDO = new \PDO($dataSource->getDriverName().$settings, $dataSource->getUserName(), $dataSource->getPassword(), $dataSource->getDriverOptions());
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->PDO->beginTransaction();
    }

    /**
     * Executes query and applies user defined validator to results.
     *
     * @param string $query
     * @param ResultValidator $validator Algorithm to validate SQL execution results.
     * @return Result
     */
    public function assertStatement(string $query, ResultValidator $validator): Result
    {
        try {
            return $validator->validate($this->PDO->query($query));
        } catch (\Exception $e) {
            $this->unitTestResult = new Result(false, $e->getMessage());
        }
    }
    
    /**
     * Executes query and applies user defined validator to results.
     *
     * @param string $query
     * @param string[string] $boundParameters
     * @param ResultValidator $validator Algorithm to validate SQL execution results.
     * @return Result
     */
    public function assertPreparedStatement(string $query, array $boundParameters, ResultValidator $validator): Result
    {
        try {
            $statement = $this->PDO->prepare($query);
            return $validator->validate($statement->execute($boundParameters));
        } catch (\Exception $e) {
            $this->unitTestResult = new Result(false, $e->getMessage());
        }
    }
    
    /**
     * Automatically rolls back transaction and closes connection when script ends
     */
    public function __destruct()
    {
        $this->PDO->rollBack();
        $this->PDO = null;
    }
}
