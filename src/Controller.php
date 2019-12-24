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
        
        $namespaces = [];
        $apis = $configuration->getAPIs();
        foreach ($apis as $api) {
            $namespaces[$api->getSourcesNamespace()] = $api->getSourcesPath();
            $namespaces[$api->getTestsNamespace()] = $api->getTestsPath();
        }
        
        spl_autoload_register(function($className) use ($namespaces) {
            foreach ($namespaces as $namespace=>$folder) {
                $position = strpos($className, $namespace);
                if ($position === 0) {
                    $className = substr($className, strlen($namespace));
                    $fileName = $folder."/".str_replace("\\", "/", $className).".php";
                    if (file_exists($fileName)) {
                        require_once $fileName;
                        return;
                    }
                }
            }
        });
        
        new Creator($configuration);
        
        $runner = new Runner($configuration);
        $results = $runner->getResults();
        
        $this->handle($results);
    }
    
    /**
     * Handles unit test results by storing or displaying them.
     * 
     * @param Result[] $results
     */
    abstract protected function handle(array $results): void;
}

