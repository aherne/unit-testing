<?php
namespace Lucinda\UnitTest;

/**
 * Controls unit tests creation, execution and display
 */
abstract class Controller
{
    
    /**
     * Reads unit tests configuration based on XML file and development environment
     *
     * @param string $xmlFilePath
     * @param string $developmentEnvironment
     * @throws Exception
     */
    public function __construct(string $xmlFilePath, string $developmentEnvironment)
    {
        $configuration = new Configuration($xmlFilePath, $developmentEnvironment);
        
        new Creator($configuration);
        
        $runner = new Runner($configuration);
        $this->handle($runner->getResults());
    }
    
    /**
     * Handles unit test results by storing or displaying them.
     *
     * @param Result[] $results
     */
    abstract protected function handle(array $results): void;
}
