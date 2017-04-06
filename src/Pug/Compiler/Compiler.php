<?php

namespace Pug\Compiler;

class Compiler
{
    /**
     * A map of scopes to compiler classes.
     *
     * @var string[]
     */
    public static $classMap = [
        'pages'  => PageCompiler::class,
    ];
}
