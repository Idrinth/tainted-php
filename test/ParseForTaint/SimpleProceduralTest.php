<?php

namespace De\Idrinth\Test\TaintedPhp\ParseForTaint;

use De\Idrinth\Test\TaintedPhp\ParseForTaintTest;

class SimpleProceduralTest extends ParseForTaintTest
{
    private static $simpleProcedural = [
        'mysql_query()#1' => ['doWork()$zzz'],
        'mysqli_query()#1' => ['handle()$z'],
        'eval()#0' => ['handle()'],
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
        'include()#0' => ['$b'],
        'include_once()#0' => [],
        '$a' => [],
        '$b' => ['$_POST'],
        '$c' => ['a_func()', 'file_get_contents()'],
        'a_func()' => [],
        'handle()#0' => ['$c'],
        'handle()$z' => ['handle()#0'],
        'handle()$a' => ['global'],
        'handle()$link' => ['global'],
        'handle()$r' => ['handle()$a'],
        'handle()$d' => ['mysqli_fetch_all()'],
        'mysqli_fetch_all()#0' => ['mysqli_query()'],
        'mysqli_query()' => [],
        'mysqli_query()#0' => ['handle()$link'],
        'handle()' => ['handle()$r', 'handle()$d'],
        'eval()' => [],
        'doWork()#0' => [],
        'doWork()$zzz' => ['doWork()#0'],
        'doWork()$link' => ['global'],
        'doWork()' => ['mysql_query()'],
        'mysql_query()' => [],
        'mysql_query()#0' => ['doWork()$link'],
        '$q' => [],
        'include()' => [],
    ];
    protected function getExpectedOutcome()
    {
        return self::$simpleProcedural;
    }
}
