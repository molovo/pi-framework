<?php

namespace Pug\Compiler\Traits;

use Pug\Framework\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait AssetCompiler
{
    /**
     * An array of source file names.
     *
     * @var string[]
     */
    protected $files = [];

    /**
     * The destination in which files will be stored.
     *
     * @var string|null
     */
    protected $dest = null;

    /**
     * Create a new instance of the compiler.
     *
     * @param Config $config Configuration for the compiler
     */
    public function __construct(Config $config)
    {
        // Store the config
        $this->config = $config;

        // Get and store the source files array
        if (!is_array($config->src)) {
            $config->src = [$config->src];
        }
        $this->files = $this->prepareSourceFiles($config->src);

        // Store the destination
        $this->dest = $config->dest;

        if (!is_dir($this->dest)) {
            mkdir($this->dest, 0700, true);
        }

        $this->clean();
    }

    /**
     * Empty the destination directory prior to compiling.
     */
    protected function clean()
    {
        if (!$this->config->clean) {
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dest));

        foreach ($files as $file) {
            // var_dump($file);
            if (!in_array($file->getFilename(), ['.', '..'])) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Get a list of files matching the passed in glob strings.
     *
     * @param string[] $paths The glob paths to check
     *
     * @return string[] The full list of files
     */
    protected function prepareSourceFiles($paths)
    {
        $files = [];

        // Loop through each of the source files
        foreach ($paths as $path) {
            // Pass to glob to get a list of matching filenames
            $matches = glob($path);

            // Store the filenames
            $files = $files + $matches;
        }

        return $files;
    }
}
