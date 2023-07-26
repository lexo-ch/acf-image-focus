<?php

namespace LEXO\AcfIF\Core\Abstracts;

abstract class Singleton
{
    protected static $instance = null;

    public static function getInstance()
    {
        return (static::$instance === null)
            ? static::$instance = new static()
            : static::$instance;
    }

    private function __construct()
    {
        /**
         * Make constructor private, so nobody can call "new Class".
         */
    }

    private function __clone()
    {
        /**
         * Make clone magic method private, so nobody can clone instance.
         */
    }
}
