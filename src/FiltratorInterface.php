<?php

namespace Sirius\Filtration;

interface FiltratorInterface {

    function add($selector, $callbackOrFilterName = null, $options = null, $recursive = false, $priority = 0);

    function remove($selector, $callbackOrName = true);

    function filter($data = array());
}