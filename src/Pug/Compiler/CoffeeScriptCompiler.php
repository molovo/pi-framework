<?php

namespace Pug\Compiler;

use CoffeeScript\Compiler as CoffeeScript;
use JShrink\Minifier;
use Pug\Compiler\Interfaces\AssetCompiler as AssetCompilerInterface;
use Pug\Compiler\Traits\AssetCompiler;

class CoffeeScriptCompiler implements AssetCompilerInterface
{
    use AssetCompiler;

    /**
     * Compile the site's CoffeeScript into JavaScript.
     */
    public function compile()
    {
        $minify = $this->config->minify;

        // If we are concatenating files, then open a file pointer
        if ($concatenate = $this->config->concatenate) {
            $output = fopen($this->dest.'/'.$this->config->output, 'w');
        }

        // Loop through each of the input files
        foreach ($this->files as $file) {
            // Get the contents of the file
            $coffee = file_get_contents($file);

            // Convert CoffeeScript to JS
            $js = CoffeeScript::compile($coffee, ['filename' => $file]);

            // Minify if necessary
            if ($minify) {
                $js = $this->minify($js);
            }

            // If concatenating, write to the file pointer
            if ($concatenate) {
                fputs($output, $js);
                continue;
            }

            // Replace the file extension
            $file = str_replace('.coffee', '.js', $file);

            // Write a new file to the output directory
            file_put_contents($this->dest.'/'.basename($file), $js);
        }

        // Close the file pointer
        if ($concatenate) {
            fclose($output);
        }
    }

    /**
     * Minify the JS.
     *
     * @param string $js JS to minify
     *
     * @return string Minified JS
     */
    private function minify($js)
    {
        return Minifier::minify($js);
    }
}
