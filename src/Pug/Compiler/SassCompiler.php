<?php

namespace Pug\Compiler;

use Pug\Compiler\Interfaces\AssetCompiler as AssetCompilerInterface;
use Pug\Compiler\Traits\AssetCompiler;

class SassCompiler implements AssetCompilerInterface
{
    use AssetCompiler;

    /**
     * Compile the site's CSS.
     */
    public function compile()
    {
        $minify = $this->config->minify;

        $style  = $minify ? 'compressed' : 'nested';
        $parser = new \SassParser(['style' => $style]);

        // If we are concatenating files, then open a file pointer
        if ($concatenate = $this->config->concatenate) {
            $output = fopen($this->dest.'/'.$this->config->output, 'w');
        }

        // Loop through each of the input files
        foreach ($this->files as $file) {
            // Get the contents of the file
            $sass = file_get_contents($file);

            $css = $parser->toCss($file);

            // Minify if necessary
            if ($minify) {
                $css = $this->minify($css);
            }

            // If concatenating, write to the file pointer
            if ($concatenate) {
                fwrite($output, $css);
                continue;
            }

            // Replace the file extension
            $file = basename(str_replace('.sass', '.css', $file));

            // Write a new file to the output directory
            file_put_contents($this->dest.'/'.$file, $css);
        }

        // Close the file pointer
        if ($concatenate) {
            fclose($output);
        }
    }

    /**
     * Quick and dirty way to mostly minify CSS.
     *
     * @param string $css CSS to minify
     *
     * @return string Minified CSS
     */
    private function minify($css)
    {
        // Normalize whitespace
        $css = preg_replace('/\s+/', ' ', $css);

        // Remove spaces before and after comment
        $css = preg_replace('/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css);

        // Remove comment blocks, everything between /* and */, unless
        // preserved with /*! ... */ or /** ... */
        $css = preg_replace('~/\*(?![\!|\*])(.*?)\*/~', '', $css);

        // Remove ; before }
        $css = preg_replace('/;(?=\s*})/', '', $css);

        // Remove space after , : ; { } */ >
        $css = preg_replace('/(,|:|;|\{|}|\*\/|>) /', '$1', $css);

        // Remove space before , ; { } ( ) >
        $css = preg_replace('/ (,|;|\{|}|\(|\)|>)/', '$1', $css);

        // Strips leading 0 on decimal values (converts 0.5px into .5px)
        $css = preg_replace('/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css);

        // Strips units if value is 0 (converts 0px to 0)
        $css = preg_replace('/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css);

        // Converts all zeros value into short-hand
        $css = preg_replace('/0 0 0 0/', '0', $css);

        // Shortern 6-character hex color codes to 3-character where possible
        $css = preg_replace('/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css);

        return trim($css);
    }
}
