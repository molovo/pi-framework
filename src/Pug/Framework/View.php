<?php

namespace Pug\Framework;

use Parsedown;
use Pug\Framework\Exceptions\View\ViewNotFoundException;

class View
{
    /**
     * The directory in which views are found.
     */
    const VIEW_DIR = APP_ROOT.'views';

    /**
     * Global variables which are included in all views.
     *
     * @var array
     */
    public static $globals = [];

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
     * Create a new view.
     *
     * @param string $name The name of the view
     * @param array  $vars The variables to include in the view
     */
    public function __construct($name, array $vars = [])
    {
        $this->name = $name;

        $path = APP_ROOT.'views'.DS.$name;

        $files = array_merge(glob($path.'.*'), glob($path));

        foreach ($files as $i => $file) {
            if (is_dir($file)) {
                unset($files[$i]);
            }
        }

        if (sizeof($files) > 1) {
            throw new ViewNotFoundException('Name "'.$name.'" matches multiple views. Please be more specific.');
        }

        if (sizeof($files) === 0) {
            throw new ViewNotFoundException('The view "'.$name.'" does not exist.');
        }

        $this->file = $files[0];
        $this->type = pathinfo($this->file, PATHINFO_EXTENSION);
        $this->vars = array_merge(static::$globals, $vars);
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
    public function render($useCached = true)
    {
        if ($useCached && $this->content !== null) {
            return $this->content;
        }

        if (isset($this->vars['layout'])) {
            $this->layout = $this->vars['layout'];
            unset($this->vars['layout']);
        }

        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        switch ($this->type) {
            case 'md':
            case 'markdown':
                $parser   = new Parsedown;
                $markdown = file_get_contents($this->file);

                $this->content = $parser->text($markdown);
                break;
            case 'php':
            default:
                ob_start();
                include $this->file;

                $this->content = ob_get_clean();
                break;
        }

        if ($this->layout !== null) {
            $vars            = $this->vars;
            $vars['content'] = $this->content;

            return (new self($this->layout, $vars))->render();
        }

        return $this->content;
    }
}
