<?php

namespace De\Idrinth\Test\TaintedPhp;

use De\Idrinth\TaintedPhp\ParseForTaint;
use De\Idrinth\TaintedPhp\TaintedIf;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ParseForTaintTest extends TestCase
{
    private static $simpleProcedural = [
        'mysql_query()#1' => ['$a'],
        'mysqli_query()#1' => [],
        'eval()#0' => [],
        'file_get_contents()#0' => [],
        'file_put_contents()#0' => [],
        'exec()#0' => [],
        'system()#0' => [],
        'proc_open()#0' => [],
        'passthru()#0' => [],
        'shell_exec()#0' => [],
        'PDO->query()#0' => [],
        'PDO->execute()#0' => [],
        'PDO->prepare()#0' => [],
        'basename()#0' => [],
        'dirname()#0' => [],
        'file()#0' => [],
        'fopen()#0' => [],
        'require()#0' => [],
        'require_once()#0' => [],
        'include()#0' => [],
        'include_once()#0' => [],
        '$a' => [],
        '$b' => [],
        '$c' => [],
        'a_func()' => [],
        'handle()#0' => [],
        'handle()$z' => [],
        'handle()$a' => [],
        'handle()$link' => [],
        'handle()$r' => [],
        'handle()$d' => [],
        'mysqli_fetch_all()#0' => [],
        'mysqli_query()' => [],
        'mysqli_query()#0' => [],
        'handle()' => [],
        'eval()' => [],
        'doWork()#0' => [],
        'doWork()$zzz' => [],
        'doWork()$link' => [],
        'doWork()' => [],
        'mysql_query()' => [],
        'mysql_query()#0' => [],
        '$q' => [],
        'require()' => [],
    ];

    private function assertTaintedIfStructure(array $expected, TaintedIf $taintedIf)
    {
        $prop  = new ReflectionProperty(TaintedIf::class, 'taintedBy');
        $prop->setAccessible(true);
        $value = $prop->getValue($taintedIf);
        static::assertCount(
            count($expected),
            $value,
            "Count of {$taintedIf->getName()} didn't match expectation."
        );
        static::assertContainsOnly(
            TaintedIf::class,
            $value,
            false,
            "Some Elements of {$taintedIf->getName()} were of a wrong type."
        );
        $has   = [];
        foreach ($value as $tainter) {
            $has[] = $tainter->getName();
        }
        $this->assertArrayStructure(
            $expected,
            $has,
            "There are different elements tainting {$taintedIf->getName()}: "
        );
    }

    private function assertArrayStructure(array $expected, $has, $text)
    {
        $missing    = array_diff($expected, $has);
        $additional = array_diff($has, $expected);
        static::assertEquals(
            0,
            count($missing) + count($additional),
            $text.json_encode(['additional' => array_values($additional), 'missing' => array_values($missing)])
        );
    }

    public function testSimpleProcedural()
    {
        $parser = new ParseForTaint();
        $result = $parser->parse(__DIR__.'/files/simple-procedural.php');
        static::assertContainsOnly(
            TaintedIf::class,
            $result,
            false,
            "Some Elements of result were of a wrong type."
        );
        $this->assertArrayStructure(
            array_keys(self::$simpleProcedural),
            array_keys($result),
            "Result: "
        );
        foreach (self::$simpleProcedural as $name => $structure) {
            $this->assertTaintedIfStructure($structure, $result[$name]);
        }
    }
}
