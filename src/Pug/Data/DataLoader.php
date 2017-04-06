<?php

namespace Pug\Data;

use Closure;
use Pug\Framework\Exceptions\FileNotFoundException;

class DataLoader
{
    /**
     * The source directory for datafiles.
     *
     * @var string
     */
    const SRC = APP_ROOT.'data';

    /**
     * The CSV file type.
     *
     * @var string
     */
    const CSV  = 'csv';

    /**
     * The JSON file type.
     *
     * @var string
     */
    const JSON = 'json';

    /**
     * The YML file type.
     *
     * @var string
     */
    const YAML  = 'yml';

    /**
     * The XML file type.
     *
     * @var string
     */
    const XML  = 'xml';

    /**
     * An array of allowed file types.
     *
     * @var string
     */
    const ALLOWED_FILE_TYPES = [self::CSV, self::JSON, self::YAML, self::XML];

    /**
     * An map of file types to their respective load methods.
     *
     * @var string[]
     */
    const LOAD_METHODS = [
        self::CSV  => 'loadCsv',
        self::JSON => 'loadJson',
        self::YAML => 'loadYaml',
        self::XML  => 'loadXml',
    ];

    /**
     * The filename as provided by the user.
     *
     * @var string
     */
    private $filename;

    /**
     * The full file path.
     *
     * @var string
     */
    private $filepath;

    /**
     * The file we are reading from.
     *
     * @var resource
     */
    private $file;

    /**
     * The type of the file we're reading.
     *
     * @var string
     */
    private $type;

    /**
     * Load data from a file.
     *
     * @param string       $filename
     * @param Closure|null $modifier a callback to apply to each
     *                               row in the dataset
     *
     * @return array|null
     */
    public static function loadFromFile($filename, Closure $modifier = null)
    {
        $loader = new static($filename);

        return $loader->load($modifier);
    }

    /**
     * Create a new loader instance.
     *
     * @param string $filename
     * @param string $src      The source directory which the file
     *                         can be found within
     */
    public function __construct($filename, $src = self::SRC)
    {
        $this->parseFilename($filename, $src);
    }

    public function load(Closure $modifier = null)
    {
        $loadMethod = self::LOAD_METHODS[$this->type] ?? null;

        if ($loadMethod === null || !method_exists(self::class, $loadMethod)) {
            throw new FileNotFoundException("Could not find dataloader for $this->type file type");
        }

        return $this->{$loadMethod}($modifier);
    }

    private function parseFilename($filename, $src = self::SRC)
    {
        $this->filename = $filename;
        $this->filepath = $src.'/'.$filename;

        $this->type = pathinfo($this->filepath, PATHINFO_EXTENSION);

        if (!$this->type) {
            $this->filepath .= '.*';

            $files = glob($this->filepath);

            if (empty($files)) {
                throw new FileNotFoundException("The datafile $this->filename could not be found");
            }

            if (count($files) > 1) {
                throw new FileNotFoundException("The datafile $this->filename is ambiguous. Please specify an extension");
            }

            $this->filepath = $files[0];
            $this->filename = basename($this->filepath);
            $this->type     = pathinfo($this->filepath, PATHINFO_EXTENSION);
        }

        if (!in_array($this->type, self::ALLOWED_FILE_TYPES)) {
            throw new FileNotFoundException("File type $this->type not supported");
        }

        if (!file_exists($this->filepath)) {
            throw new FileNotFoundException("The datafile $this->filename could not be found");
        }
    }

    private function loadCsv(Closure $modifier = null)
    {
        $this->file = fopen($this->filepath, 'r');

        $rtn = [];

        while ($row = fgetcsv($this->file, 2048)) {
            if ($modifier instanceof Closure) {
                $row = $modifier($row);
            }

            if ($row !== null) {
                $rtn[] = $row;
            }
        }

        return $rtn;
    }
}
