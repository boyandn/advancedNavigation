<?php

class FrontController extends FrontControllerCore
{
    protected function canonicalRedirection($canonical_url = '')
    {
        $_old_GET = $_GET;
        $_GET = array_filter($_GET, function ($v) {
            if (is_array($v)) return false;
            return '_rewrite' === substr($v, -8);
        });
        parent::canonicalRedirection($canonical_url);
        $_GET = $_old_GET;
    }
}
