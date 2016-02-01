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
        'css'    => CssCompiler::class,
        'coffee' => CoffeeScriptCompiler::class,
        'js'     => JsCompiler::class,
        'sass'   => SassCompiler::class,
        'pages'  => PageCompiler::class,
    ];
}
