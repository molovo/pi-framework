<?php

namespace Pug\Framework;

use Mustache_Engine as Mustache;
use Mustache_Loader_FilesystemLoader as MustacheLoader;
use Parsedown;
use Pug\Framework\Exceptions\View\ViewNotFoundException;

class View
{
    /**
     * The directory in which views are found.
     */
    const VIEW_DIR = APP_ROOT.'views/';

    /**
     * The directory in which cached views are stored.
     */
    const CACHE_DIR = APP_ROOT.'storage/views/';

    /**
     * Global variables which are included in all views.
     *
     * @var array
     */
    public static $globals = [];

    /**
     * Global rendering options which are used for all views.
     *
     * @var array
     */
    public static $globalOptions = [];

    /**
     * The full file path for the view.
     *
     * @var string|null
     */
    private $file = null;

    /**
     * The compiled content of the view.
     *
     * @var string|null
     */
    private $content = null;

    /**
     * The layout within which this view will be nested.
     *
     * @var string|null
     */
    private $layout = null;

    /**
     * The vars which will be passed to view templates.
     *
     * @var array
     */
    private $vars = [];

    /**
     * The options which define how the view is rendered.
     *
     * @var array
     */
    private $options = [];

    /**
     * Create a new view.
     *
     * @param string $name The name of the view
     * @param array  $vars The variables to include in the view
     */
    public function __construct($name, array $vars = [], array $options = [])
    {
        $this->name = $name;

        $path = self::VIEW_DIR.$name;

        $files = array_merge(glob($path.'.*'), glob($path));

        foreach ($files as $i => $file) {
            if (is_dir($file)) {
                unset($files[$i]);
            }
        }

        if (count($files) > 1) {
            throw new ViewNotFoundException('Name "'.$name.'" matches multiple views. Please be more specific.');
        }

        if (count($files) === 0) {
            throw new ViewNotFoundException('The view "'.$name.'" does not exist.');
        }

        $this->file    = $files[0];
        $this->type    = pathinfo($this->file, PATHINFO_EXTENSION);
        $this->vars    = array_merge(static::$globals, $vars);
        $this->options = array_merge(static::$globalOptions, $options);

        if (isset($this->options['layout'])) {
            $this->layout = $this->options['layout'];
            unset($this->options['layout']);
        }
    }

    /**
     * Add a global variable to be available in all views.
     *
     * @param string $key   The key to set
     * @param mixed  $value The value to set
     */
    public static function addGlobal($key, $value)
    {
        static::$globals[$key] = $value;
    }

    /**
     * Remove a global variable.
     *
     * @param string $key The key to remove
     */
    public static function removeGlobal($key)
    {
        if (isset(static::$globals[$key])) {
            unset(static::$globals[$key]);
        }
    }

    /**
     * Add a global option to be set for all views.
     *
     * @param string $key   The key to set
     * @param mixed  $value The value to set
     */
    public static function setGlobalOption($key, $value)
    {
        static::$globalOptions[$key] = $value;
    }

    /**
     * Unset a global option.
     *
     * @param string $key The key to unset
     */
    public static function unsetGlobalOption($key)
    {
        if (isset(static::$globalOptions[$key])) {
            unset(static::$globalOptions[$key]);
        }
    }

    /**
     * Set the layout within which the view will be nested.
     *
     * @param string $name The path to the layout view
     */
    public function setLayout($name)
    {
        $this->layout = $name;
    }

    /**
     * Render the contents of a view.
     *
     * @param bool $useCached Whether to use the cached version if available
     *
     * @return string
     */
    public function render(array $vars = [], $useCached = false)
    {
        if ($useCached && $this->content !== null) {
            return $this->content;
        }

        $vars = array_merge($this->vars, $vars);

        switch ($this->type) {

            // The view is a markdown file
            case 'md':
            case 'markdown':
                // Set up the Parsedown parses
                $parser = new Parsedown;

                // Load the contents of the view
                $markdown = file_get_contents($this->file);

                // Render and store the HTML
                $this->content = $parser->text($markdown);
                break;

            // The view is a mustache template
            case 'mhtml':
            case 'mustache':
                // Set up the Mustache parser
                $parser = new Mustache([
                    // Set the cache directory for increased performance
                    'cache' => self::CACHE_DIR,

                    // Load partials from our views directory
                    'loader' => new MustacheLoader(self::VIEW_DIR, [
                        // We've already added the extension when determining
                        // the rendering engine to use, so stop Mustache from
                        // adding another one
                        'extension' => '',
                    ]),
                ]);
                $this->content = $parser->render($this->file, $vars);
                break;

            // The view is a simple PHP file
            case 'php':
            default:
                // Start an output buffer so that view contents can
                // be captured
                ob_start();

                // Create a closure so that vars can be extracted without
                // conflicts with variables in the parent scope
                $render = function ($vars, $file) {
                    // Extract the vars into local variables so that
                    // they are accessible in our included view.
                    extract($vars);

                    // Include the view
                    include $this->file;

                    // Return the contents of the output buffer
                    return ob_get_clean();
                };

                // Render and store the content
                $this->content = $render($this->vars, $this->file);
                break;
        }

        if ($this->layout !== null) {
            $vars['content'] = $this->content;

            return (new self($this->layout, $vars))->render();
        }

        return $this->content;
    }
}
