<?php

namespace Sirius\Filtration;

use Sirius\Filtration\Filtrator;

trait FiltratableTrait {
    protected $filtrator;

    function setValidator($filtrator) {
        if (!$filtrator instanceof Filtrator) {
            throw new \InvalidArgumentException('The $filtrator argument is not a proper Filtrator object');
        }
        $this->filtrator = $filtrator;
        return $this;
    }

    function getValidator($filtrator) {
        if (!$this->filtrator) {
            $this->filtrator = new Filtrator();
        }
        return $this->filtrator;
    }
}
