<?php

namespace Pi\Framework;

use Pi\Framework\Exceptions\View\ViewNotFoundException;

class View {
    /**
     * The directory in which views are found
     */
    const VIEW_DIR = APP_ROOT.'views';

    /**
     * Global variables which are included in all views
     *
     * @var array
     */
    public static $globals = [];

    /**
     * The full file path for the view
     *
     * @var string|null
     */
    private $file = null;

    /**
     * The compiled content of the view
     *
     * @var string|null
     */
    private $content = null;

    /**
     * Create a new view
     *
     * @param string $name The name of the view
     * @param array $vars The variables to include in the view
     */
    public function __construct($name, array $vars = []) {
        $this->name = $name;
        $this->file = APP_ROOT.'views'.DS.$name.'.php';
        $this->vars = array_merge(static::$globals, $vars);

        if (!file_exists($this->file)) {
            throw new ViewNotFoundException('The view "'.$name.'" does not exist');
        }
    }

    /**
     * Add a global variable to be available in all views
     *
     * @param string $key   The key to set
     * @param mixed $value The value to set
     */
    public static function addGlobal($key, $value) {
        static::$globals[$key] = $value;
    }

    /**
     * Remove a global variable
     *
     * @param  string $key The key to remove
     */
    public static function removeGlobal($key) {
        if (isset(static::$globals[$key])) {
            unset(static::$globals[$key]);
        }
    }

    /**
     * Render the contents of a view
     *
     * @param  bool $useCached Whether to use the cached version if available
     *
     * @return string
     */
    public function render($useCached = true) {
        if ($useCached && $this->content !== null) {
            return $this->content;
        }

        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include $this->file;
        return $this->content = ob_get_clean();
    }
}
