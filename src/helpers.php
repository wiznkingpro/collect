<?php

use Wiznkingpro\Collect\Collect;

if (!function_exists('collect')) {
    function collect($items = []): Collect
    {
        return new Collect($items);
    }
}
