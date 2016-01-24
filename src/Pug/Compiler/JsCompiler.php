<?php

namespace Pug\Compiler;

use JShrink\Minifier;
use Pug\Compiler\Interfaces\AssetCompiler as AssetCompilerInterface;
use Pug\Compiler\Traits\AssetCompiler;

class JsCompiler implements AssetCompilerInterface
{
    use AssetCompiler;

    /**
     * Compile the site's JavaScript.
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
            $css = file_get_contents($file);

            // Minify if necessary
            if ($minify) {
                $css = $this->minify($css);
            }

            // If concatenating, write to the file pointer
            if ($concatenate) {
                fputs($output, $css);
                continue;
            }

            // Write a new file to the output directory
            file_put_contents($this->dest.'/'.$file, $css);
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
