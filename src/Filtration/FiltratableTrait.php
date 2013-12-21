<?php

namespace Sirius\Filtration;

use Sirius\Filtration\Filtrator;

trait FiltratableTrait {
    protected $filtrator;

    function setFiltrator(Filtrator $filtrator) {
        $this->filtrator = $filtrator;
        return $this;
    }

    function getFiltrator($filtrator) {
        if (!$this->filtrator) {
            $this->filtrator = new Filtrator();
        }
        return $this->filtrator;
    }
}
