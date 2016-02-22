<?php

namespace Pug\Framework;

use Molovo\Amnesia\Cache;
use Molovo\Str\Str;
use Mustache_Engine as Mustache;
use Mustache_Loader_CascadingLoader;
use Mustache_Loader_FilesystemLoader;
use Parsedown;
use Pug\Framework\Exceptions\View\InvalidOptionException;
use Pug\Framework\Exceptions\View\ViewNotFoundException;

class View
{
    /**
     * The directory in which views are found.
     */
    const VIEW_DIR = APP_ROOT.'views/';

    /**
     * The directory in which compiled Mustache classes are stored.
     */
    const CACHE_DIR = APP_ROOT.'storage/views/';

    /**
     * The key under which rendered views will be stored in the cache.
     */
    const CACHE_KEY = 'views.';

    /**
     * Global variables which are included in all views.
     *
     * @var array
     */
    private static $globals = [];

    /**
     * Global rendering options which are used for all views.
     *
     * @var array
     */
    private static $globalOptions = [];

    /**
     * Default rendering options.
     *
     * @var array
     */
    private static $defaultOptions = [
        'layout' => null,
    ];

    /**
     * The parser for rendering markdown views.
     *
     * @var Parsedown|null
     */
    private static $markdownParser = null;

    /**
     * The parser for rendering mustache templates.
     *
     * @var Mustache|null
     */
    private static $mustacheParser = null;

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
     * Whether the view should be cached.
     *
     * @var bool
     */
    private $cache = false;

    /**
     * Cache expiry time.
     *
     * @var int|null
     */
    private $cache_expiry = null;

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

        $this->file = $files[0];
        $this->type = pathinfo($this->file, PATHINFO_EXTENSION);
        $this->vars = array_merge(
            static::$globals,
            $vars
        );
        $this->options = array_merge(
            static::$defaultOptions,
            static::$globalOptions,
            $options
        );
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
        if (!array_key_exists($key, static::$defaultOptions)) {
            throw new InvalidOptionException('The option '.$key.' is invalid.');
        }
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
        $this->options['layout'] = $name;

        return $this;
    }

    /**
     * Retrieve the value of an option.
     *
     * @param string $key The option to get
     *
     * @return mixed
     */
    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        return;
    }

    /**
     * Get all the view's options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set a rendering option.
     *
     * @param string $key   The option key to set
     * @param string $value The value
     */
    public function setOption($key, $value)
    {
        if (!array_key_exists($key, static::$defaultOptions)) {
            throw new InvalidOptionException('The option "'.$key.'" is invalid.');
        }

        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set multiple rendering options.
     *
     * @param array $options An array of options to set
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            if (!array_key_exists($key, static::$defaultOptions)) {
                throw new InvalidOptionException('The option "'.$key.'" is invalid.');
            }

            $this->options[$key] = $value;
        }

        return $this;
    }

    /**
     * Retrieve the value of a variable.
     *
     * @param string $key The var to get
     *
     * @return mixed
     */
    public function getVar($key)
    {
        if (isset($this->vars[$key])) {
            return $this->vars[$key];
        }

        return;
    }

    /**
     * Get all the view's variables.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Add a single variable.
     *
     * @param string $key   The variable key to add
     * @param string $value The value
     */
    public function addVar($key, $value)
    {
        $this->vars[$key] = $value;

        return $this;
    }

    /**
     * Remove a single variable.
     *
     * @param string $key The variable key to remove
     */
    public function removeVar($key)
    {
        if (isset($this->vars[$key])) {
            unset($this->vars[$key]);
        }

        return $this;
    }

    /**
     * Add multiple variables.
     *
     * @param array $vars An array of variables to add
     */
    public function addVars($vars)
    {
        $this->vars = array_merge($this->vars, $vars);

        return $this;
    }

    /**
     * Cache the view.
     *
     * @param int|null $expiry Optional expiry time (in seconds)
     *
     * @return self
     */
    public function cache($expiry = null)
    {
        $this->cache        = true;
        $this->cache_expiry = $expiry !== null ? (int) $expiry : null;

        return $this;
    }

    /**
     * Get a safe cache key.
     *
     * @return string The cache key
     */
    private function cacheKey()
    {
        return 'views.'.Str::slug($this->name);
    }

    /**
     * Render the contents of a view.
     *
     * @param array $vars        Variables to pass to views
     * @param bool  $bypassCache Whether to bypass the cache
     *
     * @return string The rendered HTML
     */
    public function render(array $vars = [], $bypassCache = false)
    {
        // Check the cache
        if ($this->cache && !$bypassCache) {
            if (($content = Cache::get($this->cacheKey())) !== null) {
                return $content;
            }
        }

        $vars = array_merge($this->vars, $vars);

        switch ($this->type) {

            // The view is a markdown file
            case 'md':
            case 'markdown':
                $this->content = $this->renderMarkdown($vars);
                break;

            // The view is a mustache template
            case 'mhtml':
            case 'mustache':
                $this->content = $this->renderMustache($vars);
                break;

            // The view is a simple PHP file
            case 'php':
            default:
                $this->content = $this->renderPhp($vars);
                break;
        }

        $vars = array_merge($this->vars, $vars);

        if ($this->options['layout'] !== null) {
            $vars['content'] = $this->content;

            $this->content = (new self($this->options['layout'], $vars))->render();
        }

        if ($this->cache) {
            Cache::set($this->cacheKey(), $this->content, $this->cache_expiry);
        }

        return $this->content;
    }

    /**
     * Render a PHP view.
     *
     * @param array $vars The variables to include in the view
     *
     * @return string The rendered HTML
     */
    public function renderPhp(array $vars = [])
    {
        $vars = array_merge($this->vars, $vars);

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
            include $file;

            // Return the contents of the output buffer
            return ob_get_clean();
        };

        // Render and store the content
        return $this->content = $render($vars, $this->file);
    }

    /**
     * Separate YAML front matter from content and set options,
     * then return the remaining content.
     *
     * @param string $source The source text
     *
     * @return string The rest of the file content
     */
    private function parseFrontMatter($source)
    {
        $parts = preg_split('/[\n]*[-]{3}[\n]/', $source, 3);

        // If there's only one item in the array, or the first item isn't empty,
        // then there isn't any YAML front matter, and we can just process the
        // source file in it's raw form
        if (count($parts) === 1 || !empty($parts[0])) {
            return $source;
        }

        list(, $yaml, $content) = $parts;

        $options = Yaml::parse($yaml);

        foreach ($options as $key => $value) {
            if (array_key_exists($key, static::$defaultOptions)) {
                $this->setOption($key, $value);
                continue;
            }

            $this->addVar($key, $value);
        }

        return $content;
    }

    /**
     * Render a Mustache view.
     *
     * @param array $vars The variables to include in the view
     *
     * @return string The rendered HTML
     */
    public function renderMustache(array $vars = [])
    {
        // Set up the Mustache parser
        if (static::$mustacheParser === null) {
            $loader = new Mustache_Loader_CascadingLoader([
                new Mustache_Loader_FilesystemLoader(self::VIEW_DIR, [
                    'extension' => '',
                ]),
                new Mustache_Loader_FilesystemLoader(self::VIEW_DIR, [
                    'extension' => '.mustache',
                ]),
                new Mustache_Loader_FilesystemLoader(self::VIEW_DIR, [
                    'extension' => '.mhtml',
                ]),
                new Mustache_Loader_FilesystemLoader(self::VIEW_DIR, [
                    'extension' => '.md',
                ]),
                new Mustache_Loader_FilesystemLoader(self::VIEW_DIR, [
                    'extension' => '.markdown',
                ]),
            ]);

            static::$mustacheParser = new Mustache([
                // Set the cache directory for increased performance
                'cache' => self::CACHE_DIR,

                // Load partials from our views directory
                'partials_loader' => $loader,
            ]);
        }

        $mustache = $this->parseFrontMatter(file_get_contents($this->file));

        $vars = array_merge($this->vars, $vars);

        $parser = static::$mustacheParser;

        return $this->content = $parser->render($mustache, $vars);
    }

    /**
     * Render a Markdown view.
     *
     * @param array $vars The variables to include in the view
     *
     * @return string The rendered HTML
     */
    public function renderMarkdown(array $vars = [])
    {
        // Set up the Parsedown parses
        if (static::$markdownParser === null) {
            static::$markdownParser = new Parsedown;
        }

        // Pass the raw markdown through the mustache parser
        $markdown = $this->renderMustache($vars);
        $vars     = array_merge($this->vars, $vars);

        // Render and store the HTML
        $parser = static::$markdownParser;

        return $this->content = $parser->text($markdown);
    }
}
