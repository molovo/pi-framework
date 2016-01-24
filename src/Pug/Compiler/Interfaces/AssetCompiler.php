<?php

namespace Pug\Compiler\Interfaces;

use Pug\Framework\Config;

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
