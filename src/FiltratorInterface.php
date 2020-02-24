<?php
declare(strict_types=1);

namespace Sirius\Filtration;

interface FiltratorInterface
{
    public function add($selector, $callbackOrFilterName = null, $options = null, $recursive = false, $priority = 0);

    public function remove($selector, $callbackOrName = true);

    public function filter(array $data = []);
}
