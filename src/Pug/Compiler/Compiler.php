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
        'pages'  => PageCompiler::class,
        'sass'   => SassCompiler::class,
    ];
}
