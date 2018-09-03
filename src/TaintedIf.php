<?php

namespace De\Idrinth\TaintedPhp;

class TaintedIf
{
    private $name;
    /**
     * @var TaintedIf[]
     */
    private $taintedBy = [];
    /**
     * @var TaintedIf[]
     */
    private $ifUndefindedTaintedBy = [];
    private $defined = true;
    public function __construct(string $name)
    {
        $this->name = $name;
        if (substr($name, -2) === '()') {
            $this->defined = false;
        }
    }
    public function getName()
    {
        return $this->name;
    }
    public function isTainted()
    {
        if (!$this->defined) {
            foreach ($this->taintedBy as $taintedIf) {
                if ($taintedIf->isTainted()) {
                    return true;
                }
            }
        }
        foreach ($this->taintedBy as $taintedIf) {
            if ($taintedIf->isTainted()) {
                return true;
            }
        }
        return false;
    }
    public function markDefined()
    {
        $this->defined = true;
    }
    public function mayBeTainted()
    {
        if (!$this->defined) {
            foreach ($this->taintedBy as $taintedIf) {
                if ($taintedIf->ifUndefindedTaintedBy()) {
                    return true;
                }
            }
        }
        foreach ($this->taintedBy as $taintedIf) {
            if ($taintedIf->mayBeTainted()) {
                return true;
            }
        }
        return false;
    }
    public function addTaintSource(TaintedIf $source)
    {
        if (!isset($this->taintedBy[$source->getName()])) {
            $this->taintedBy[$source->getName()] = $source;
        }
    }
    public function addUndefinedFunctionTaintSource(TaintedIf $source)
    {
        if (!isset($this->ifUndefindedTaintedBy[$source->getName()])) {
            $this->ifUndefindedTaintedBy[$source->getName()] = $source;
        }
    }
    public function toString($indent)
    {
        $content = str_repeat(' ', $indent)."$this->name";
        $tainted = $this->isTainted();
        foreach ($this->taintedBy as $taint) {
            if ($tainted === $taint->isTainted()) {
                $content .= "\n".$taint->toString($indent+2);
            }
        }
        return $content;
    }
    public function __toString()
    {
        return $this->toString(2);
    }
    public function hasTainters()
    {
        return count($this->taintedBy) > 0;
    }
}
