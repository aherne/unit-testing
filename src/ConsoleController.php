<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests on unix console and displays results
 */
class ConsoleController extends Controller
{
    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    protected function handle(array $results): void
    {
        $totals = ["passed"=>0, "failed"=>0];
        
        $columns = ["Class", "Method", "Status", "Description"];
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $object = new \Lucinda\Console\Table(array_map(function ($column) {
                $text = new \Lucinda\Console\Text($column);
                $text->setFontStyle(\Lucinda\Console\FontStyle::BOLD);
                return $text;
            }, $columns));
            foreach ($results as $unitTest) {
                $status = null;
                if ($unitTest->result->hasPassed()) {
                    $status = new \Lucinda\Console\Text(" PASSED ");
                    $status->setBackgroundColor(\Lucinda\Console\BackgroundColor::GREEN);
                } else {
                    $status = new \Lucinda\Console\Text(" FAILED ");
                    $status->setBackgroundColor(\Lucinda\Console\BackgroundColor::RED);
                }
                $object->addRow([
                    $unitTest->className,
                    $unitTest->methodName,
                    $status,
                    $unitTest->result->getMessage()
                ]);
                $totals[$unitTest->result->hasPassed()?"passed":"failed"]++;
            }
            echo $object->toString()."\n";
        } else {
            $object = new \Lucinda\Console\Table(array_map(function ($column) {
                return strtoupper($column);
            }, $columns));
            foreach ($results as $unitTest) {
                $object->addRow([
                    $unitTest->className,
                    $unitTest->methodName,
                    ($unitTest->result->hasPassed()?"PASSED":"FAILED"),
                    $unitTest->result->getMessage()
                ]);
                $totals[$unitTest->result->hasPassed()?"passed":"failed"]++;
            }
            echo $object->toString()."\n";
        }
        
        echo "Total: ".($totals["passed"]+$totals["failed"])." (".$totals["passed"]." passed, ".$totals["failed"]." failed)\n";
    }
}
