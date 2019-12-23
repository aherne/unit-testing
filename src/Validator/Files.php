<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

class Files
{
    private $path;
    
    /**
     * Constructs a integer
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
    
    /**
     * Asserts if file exists
     *
     * @param string $message
     * @return Result
     */
    public function assertExists(string $message=""): Result
    {
        return new Result(file_exists($this->path), $message);
    }
    
    /**
     * Asserts if file not exists
     *
     * @param string $message
     * @return Result
     */
    public function assertNotExists(string $message=""): Result
    {
        return new Result(!file_exists($this->path), $message);
    }
    
    /**
     * Asserts if file content equals expected string
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertEquals(string $expected, string $message=""): Result
    {
        return new Result(file_get_contents($this->path) == $expected, $message);
    }
    
    /**
     * Asserts if file content doesn't equal expected string
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertNotEquals(string $expected, string $message=""): Result
    {
        return new Result(file_get_contents($this->path) != $expected, $message);
    }
    
    /**
     * Asserts if file contains expected string
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertContains(string $expected, string $message=""): Result
    {
        return new Result(strpos(file_get_contents($this->path), $expected)!==false, $message);
    }
    
    /**
     * Asserts if file doesn't contain expected string
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertNotContains(string $expected, string $message=""): Result
    {
        return new Result(strpos(file_get_contents($this->value), $expected)===false, $message);
    }
    
    /**
     * Asserts if file is of expected size
     *
     * @param int $size
     * @param string $message
     * @return Result
     */
    public function assertSize(int $size, string $message=""): Result
    {
        return new Result(filesize($this->value)==$size, $message);
    }
    
    /**
     * Asserts if file is not of expected size
     *
     * @param int $size
     * @param string $message
     * @return Result
     */
    public function assertNotSize(int $size, string $message=""): Result
    {
        return new Result(filesize($this->value)!=$size, $message);
    }
}
