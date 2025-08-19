<?php

namespace App\Services;

use App\Models\Link;
use App\Services\Resource\Html;
use App\Services\Resource\Parser;

class Resource
{
    public static function parse(
        Link $link
    ) {
        return new Parser($link);
    }

    public static function html(
        string $html
    ) {
        return new Html($html);
    }
}
