<?php

namespace Pi\Compiler\Interfaces;

use Pi\Framework\Config;

interface AssetCompiler
{
    /**
     * Create a new instance of the compiler.
     *
     * @param Config $config Configuration for the compiler
     */
    public function __construct(Config $config);

    /**
     * Compile assets.
     */
    public function compile();
}
