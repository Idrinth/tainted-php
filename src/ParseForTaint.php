<?php

namespace De\Idrinth\TaintedPhp;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\ParserFactory;

class ParseForTaint
{
    private $elements;
    private static $globals = [
        '$_POST',
        '$_GLOBALS',
        '$_REQUEST',
        '$_GET',
        '$_SERVER',
        '$argv'
    ];
    private static $funcs = [
        'mysql_fetch_assoc()',
        'mysqli_fetch_assoc()',
        'mysql_fetch_all()',
        'mysqli_fetch_all()',
        'getcwd()',
    ];
    public function __construct(array $initialTainters = [])
    {
        $this->elements = [];
        foreach(array_merge($initialTainters, self::$funcs, self::$globals)as $element) {
            $this->elements[$element] = new AlwaysTainted($element);
        }
    }
    public function parse($file) {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse(file_get_contents($file));
        if (is_array($ast)) {
            $this->process($ast, '');
        }
        return $this->elements;
    }
    private function appendPrefix($node, $prefix)
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return ltrim(preg_replace('/(^|\\\\)[^\\\\]+?$/','\\', $prefix).'\\'.$node->name.'()', '\\');
        }
        if ($node instanceof \PhpParser\Node\Expr\Variable) {
            $var = '$'.$node->name;
            if (in_array($var, self::$globals, true)) {
                return $var;
            }
            if (preg_match('/\(\)$/', $prefix)) {
                $var = $prefix.$var;
            }
            return ltrim($var, '\\');
        }
    }
    private function process(array $ast, $prefix, $uses = [])
    {
        foreach ($ast as $node) {
            if ($node instanceof Expression) {
                if($node->expr instanceof Assign) {
                    $var = $this->appendPrefix($node->expr->var, $prefix);
                    if (!isset($this->elements[$var])) {
                        $this->elements[$var] = new TaintedIf($var);
                    }
                    if ($node->expr->expr instanceof ArrayDimFetch) {
                        $name = $this->appendPrefix($node->expr->expr->var , $prefix);
                        if (!isset($this->elements[$name])) {
                            $this->elements[$name] = new TaintedIf($name);
                        }
                        $this->elements[$var]->addTaintSource($this->elements[$name]);
                    } elseif ($node->expr->expr instanceof Variable) {
                        $name = $this->appendPrefix($node->expr->expr->var , $prefix);
                        if (!isset($this->elements[$name])) {
                            $this->elements[$name] = new TaintedIf($name);
                        }
                        $this->elements[$var]->addTaintSource($this->elements[$name]);
                    } elseif ($node->expr->expr instanceof \PhpParser\Node\Expr\FuncCall) {
                        $name = $this->appendPrefix($node->expr->expr , $prefix);
                        if (!isset($this->elements[$name])) {
                            $this->elements[$name] = new TaintedIf($name);
                        }
                        $this->elements[$var]->addTaintSource($this->elements[$name]);
                        $name2 = $this->appendPrefix($node->expr->expr , '');
                        if (!isset($this->elements[$name2])) {
                            $this->elements[$name2] = new TaintedIf($name2);
                        }
                        $this->elements[$var]->addTaintSource($this->elements[$name2]);
                    }
                }
            } elseif($node instanceof Function_) {
                $this->process($node->getStmts(), $prefix.'\\'.$node->name->name.'()', $uses);
            }
        }
    }
}