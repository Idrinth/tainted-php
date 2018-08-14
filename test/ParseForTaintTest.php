<?php

namespace De\Idrinth\Test\TaintedPhp;

use De\Idrinth\TaintedPhp\ParseForTaint;
use De\Idrinth\TaintedPhp\TaintedIf;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

abstract class ParseForTaintTest extends TestCase
{
    private function getFile()
    {
        $class = explode('\\', get_class($this));
        $class = array_pop($class);
        return __DIR__.'/files/'.$this->fromCamelCaseToSnakeCase(substr($class, 0, -4)).'.php';
    }
    private function fromCamelCaseToSnakeCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        foreach ($matches[0] as &$match) {
          $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $matches[0]);
    }
    abstract protected function getExpectedOutcome();

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

    public function testParse()
    {
        $parser = new ParseForTaint();
        $result = $parser->parse($this->getFile());
        static::assertContainsOnly(
            TaintedIf::class,
            $result,
            false,
            "Some Elements of result were of a wrong type."
        );
        $outcome = $this->getExpectedOutcome();
        $this->assertArrayStructure(
            array_keys($outcome),
            array_keys($result),
            "Result: "
        );
        foreach ($outcome as $name => $structure) {
            $this->assertTaintedIfStructure($structure, $result[$name]);
        }
    }
}
