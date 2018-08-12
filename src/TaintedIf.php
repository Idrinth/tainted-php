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
        foreach ($this->taintedBy as $taintedIf)
        {
            if($taintedIf->isTainted()) {
                return true;
            }
        }
        return false;
    }
    public function mayBeTainted()
    {
        if ($this->isTainted()) {
            return true;
        }
        foreach ($this->taintedBy as $taintedIf)
        {
            if($taintedIf->mayBeTainted()) {
                return true;
            }
        }
        return false;
    }
    public function addTaintSource(TaintedIf $source)
    {
        $this->taintedBy[] = $source;
    }
    public function __toString() {
        return "$this->name tainted: ". json_encode($this->isTainted()).' insecure: '.json_encode($this->mayBeTainted());
    }
}