<?php

namespace LEXO\AcfIF;

use const LEXO\AcfIF\{
    CACHE_KEY
};

class Deactivation
{
    public function run()
    {
        delete_transient(CACHE_KEY);
    }
}
