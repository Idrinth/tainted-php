<?php

namespace De\Idrinth\Test\TaintedPhp\ParseForTaint;

use De\Idrinth\Test\TaintedPhp\ParseForTaintTest;

class NamespacedProceduralTest extends ParseForTaintTest
{
    private static $expected = [
        'mysql_query()#1' => [],
        'mysqli_query()#1' => [],
        'eval()#0' => [],
        'file_get_contents()#0' => ['$b'],
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
        '$c' => ['a_func()', 'file_get_contents()', 'ABC\\a_func()', 'ABC\\file_get_contents()'],
        'a_func()' => [],
        '$b' => [],
        'ABC\\handle()#0' => ['$c'],
        'ABC\\handle()$z' => ['ABC\\handle()#0'],
        'ABC\\handle()$a' => ['global'],
        'ABC\\handle()' => ['ABC\\handle()$a', 'ABC\\handle()$z'],
        'system()#0' => ['ABC\\handle()', 'handle()'],
        'system()' => [],
        'handle()' => [],
        'handle()#0' => ['$c'],
        'ABC\\a_func()' => [],
        'ABC\\file_get_contents()' => [],
        'ABC\\file_get_contents()#0' => ['$b'],
        'ABC\\system()' => [],
        'ABC\\system()#0' => ['ABC\\handle()', 'handle()'],
    ];
    protected function getExpectedOutcome()
    {
        return self::$expected;
    }
}
