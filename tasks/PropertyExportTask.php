<?php

use Appcia\Webwork\Storage\Config;
use Appcia\Webwork\Storage\Config\Writer;

class PropertyExportTask extends Task
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $filter = array();

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $str
     */
    public function setFile($str)
    {
        $this->file = $str;
    }

    /**
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->filter = explode(',', $filter);
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function main()
    {
        $config = new Config();
        $properties = $this->filterProperties();

        try {
            foreach ($properties as $name => $value) {
                $config->set($name, $value);
            }
        } catch (\Exception $e) {
            throw new BuildException(sprintf("Cannot export properties to '%s'. %s", $this->file, $e->getMessage()));
        }

        try {
            $writer = Writer::create($this->file);

            $options = $this->processOptions();
            $options->inject($writer);

            $writer->write($config, $this->file);

        } catch (\Exception $e) {
            throw new BuildException(sprintf("Cannot export properties to '%s'. %s", $this->file, $e->getMessage()));
        }
    }

    /**
     * @return array
     */
    private function filterProperties()
    {
        $result = array();
        $properties = $this->project->getUserProperties();

        foreach ($properties as $name => $value) {
            $found = false;
            foreach ($this->filter as $filter) {
                $regexp = $this->processFilter($filter);

                if (preg_match($regexp, $name)) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $filter
     *
     * @return string
     */
    private function processFilter($filter)
    {
        $parts = explode('*', $filter);
        foreach ($parts as $p => $part) {
            $parts[$p] = preg_quote($part);
        }

        $regexp = '/^' . implode('(.*)', $parts) . '$/';

        return $regexp;
    }

    /**
     * @return Config
     * @throws InvalidArgumentException
     */
    private function processOptions()
    {
        $data = array();
        if (!empty($this->options)) {
            $options = explode(',', $this->options);
            foreach ($options as $option) {
                $parts = explode(':', $option);
                if (count($parts) !== 2) {
                    throw new \InvalidArgumentException("Property 'data' has invalid format.");
                }

                list ($key, $value) = $parts;
                $data[$key] = $value;
            }
        }

        $config = new Config($data);

        return $config;
    }
}