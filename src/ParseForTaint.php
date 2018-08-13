<?php

namespace De\Idrinth\TaintedPhp;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat as Concat2;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Return_;
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
        '$argv',
        'global'
    ];
    private static $funcs = [
        'mysql_fetch_assoc()',
        'mysqli_fetch_assoc()',
        'mysql_fetch_all()',
        'mysqli_fetch_all()',
        'getcwd()',
        'file_get_contents()',
    ];
    private static $mustBeUntainted = [
        'mysql_query()#1',
        'mysqli_query()#1',
        'eval()#0',
        'file_get_contents()#0',
        'file_put_contents()#0',
        'exec()#0',
        'system()#0',
        'proc_open()#0',
        'passthru()#0',
        'shell_exec()#0',
        'PDO->query()#0',
        'PDO->execute()#0',
        'PDO->prepare()#0',
        'basename()#0',
        'dirname()#0',
        'file()#0',
        'fopen()#0',
        'require()#0',
        'require_once()#0',
        'include()#0',
        'include_once()#0',
    ];
    public function __construct(array $initialTainters = [], array $forceTaintLess = [])
    {
        $this->elements = [];
        foreach (array_merge($initialTainters, self::$funcs, self::$globals) as $element) {
            $this->elements[$element] = new AlwaysTainted($element);
        }
        foreach (array_merge($forceTaintLess, self::$mustBeUntainted) as $element) {
             $this->elements[$element] = new MustBeUntainted($element);
        }
    }
    public function parse($file)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse(file_get_contents($file) ?: '');
        if (is_array($ast)) {
            $this->process($ast, '');
        }
        return $this->elements;
    }
    private function appendPrefix($node, $prefix)
    {
        if ($node instanceof FuncCall && is_string($node->name)) {
            return ltrim(preg_replace('/(^|\\\\)[^\\\\]+?$/', '\\', $prefix).'\\'.$node->name.'()', '\\');
        }
        if ($node instanceof Variable) {
            if (!is_string($node->name)) {
                return;
            }
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
    private function addVariableTaint($taints, Variable $var, $prefix)
    {
        $name = $this->appendPrefix($var, $prefix);
        if (!isset($this->elements[$name])) {
            $this->elements[$name] = new TaintedIf($name);
        }
        $this->elements[$taints]->addTaintSource($this->elements[$name]);
    }
    private function addArrayDimFetchTaint($taints, ArrayDimFetch $var, $prefix)
    {
        if ($var->var instanceof Variable) {
            $this->addVariableTaint($taints, $var->var, $prefix);
        }
    }
    private function addConcatTaint($taints, Expr $left, Expr $right, $prefix)
    {
        $this->addTaintFromExpression($taints, $left, $prefix);
        $this->addTaintFromExpression($taints, $right, $prefix);
    }
    private function addTaintFromExpression($taints, ?Expr $expression, $prefix)
    {
        if (!isset($this->elements[$taints])) {
            $this->elements[$taints] = new TaintedIf($taints);
        }
        if ($expression === null) {
            return;
        }
        if ($expression instanceof ArrayDimFetch) {
            $this->addArrayDimFetchTaint($taints, $expression, $prefix);
        } elseif ($expression instanceof Variable) {
            $this->addVariableTaint($taints, $expression, $prefix);
        } elseif ($expression instanceof FuncCall) {
            $this->addTaintFromFunctionCall($expression, $prefix, $taints);
        } elseif ($expression instanceof Concat) {
            $this->addConcatTaint($taints, $expression->left, $expression->right, $prefix);
        } elseif ($expression instanceof YieldFrom) {
            $this->addTaintFromExpression($taints, $expression->expr, $prefix);
        } elseif ($expression instanceof Yield_) {
            $this->addTaintFromExpression($taints, $expression->key, $prefix);
            $this->addTaintFromExpression($taints, $expression->value, $prefix);
        } elseif ($expression instanceof Include_) {
            $this->addTaintFromFunctionLike(
                ['include()','include_once()','require()','require_once()'][$expression->type - 1],
                [$expression->expr],
                $prefix
            );
        }
    }
    private function addTaintFromFunctionCall(FuncCall $expression, $prefix, $taints = null)
    {
        $this->addTaintFromFunctionLike($this->appendPrefix($expression, $prefix), $expression->args, $prefix, $taints);
        $this->addTaintFromFunctionLike($this->appendPrefix($expression, ''), $expression->args, $prefix, $taints);
    }
    private function addTaintFromFunctionLike($name, array $args, $prefix, $taints = null)
    {
        if (!isset($this->elements[$name])) {
            $this->elements[$name] = new TaintedIf($name);
        }
        foreach ($args as $num => $data) {
            if ($data instanceof Arg) {
                $this->addTaintFromExpression("$name#$num", $data->value, $prefix);
            } elseif ($data instanceof Expr) {
                $this->addTaintFromExpression("$name#$num", $data, $prefix);
            }
        }
        if (is_string($taints)) {
            $this->elements[$taints]->addTaintSource($this->elements[$name]);
        }
    }
    private function process(array $ast, $prefix, $uses = [])
    {
        foreach ($ast as $node) {
            if ($node instanceof Expression) {
                if ($node->expr instanceof Assign || $node->expr instanceof Concat2) {
                    $var = $this->appendPrefix($node->expr->var, $prefix);
                    $this->addTaintFromExpression($var, $node->expr->expr, $prefix);
                } elseif ($node->expr instanceof Eval_) {
                    $this->addTaintFromFunctionLike('eval()', [$node->expr->expr], $prefix);
                }
            } elseif ($node instanceof Function_) {
                $actualName = ltrim($prefix.'\\'.$node->name->name.'()', '\\');
                foreach ($node->getParams() as $num => $param) {
                    if (!isset($this->elements[$actualName."#$num"])) {
                        $this->elements[$actualName."#$num"] = new MayBeTainted($actualName."#$num");
                    }
                    $paramName = $actualName."\${$param->var->name}";
                    if (!isset($this->elements[$actualName."\${$param->var->name}"])) {
                        $this->elements[$paramName] = new TaintedIf($paramName);
                    }
                    $this->elements[$paramName]->addTaintSource($this->elements[$actualName."#$num"]);
                }
                $this->process($node->getStmts(), $actualName, $uses);
            } elseif ($node instanceof Global_) {
                foreach ($node->vars as $var) {
                    $name = $this->appendPrefix($var, $prefix);
                    if (!isset($this->elements[$name])) {
                        $this->elements[$name] = new TaintedIf($name);
                    }
                    $this->elements[$name]->addTaintSource($this->elements['global']);
                }
            } elseif ($node instanceof Return_) {
                $this->addTaintFromExpression($prefix, $node->expr, $prefix);
            } elseif ($node instanceof FuncCall) {
                $this->addTaintFromFunctionCall($node, $prefix);
            } elseif ($node instanceof Include_) {
                $this->addTaintFromFunctionLike(
                    ['include()','include_once()','require()','require_once()'][$node->type - 1],
                    [$node->expr],
                    $prefix
                );
            }
        }
    }
    public function __toString()
    {
        $content = ['tainted' => [], 'unsafe' => []];
        foreach ($this->elements as $element) {
            if ($element instanceof MustBeUntainted) {
                if ($element->isTainted()) {
                    $content['tainted'][] = $element;
                } elseif ($element->mayBeTainted()) {
                    $content['unsafe'][] = $element;
                }
            }
        }
        $string = '';
        if (count($content['tainted']) > 0) {
            $string .= "\nTAINTED\n".implode("\n", $content['tainted'])."\n";
        }
        if (count($content['unsafe']) > 0) {
            $string .= "\nUNSAFE\n".implode("\n", $content['unsafe'])."\n";
        }
        return $string;
    }
}
