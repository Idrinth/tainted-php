<?php

namespace De\Idrinth\TaintedPhp;

class TaintedIf
{
    private $name;
    /**
     * @var TaintedIf[]
     */
    private $taintedBy = [];
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function isTainted()
    {
        foreach ($this->taintedBy as $taintedIf) {
            if ($taintedIf->isTainted()) {
                return true;
            }
        }
        return false;
    }
    public function mayBeTainted()
    {
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
}
