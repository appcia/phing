<?php

use Appcia\Webwork\Storage\Config;

class PropertyImportTask extends Task
{

    /**
     * @var string
     */
    private $file;

    /**
     * @param string $str
     */
    public function setFile($str)
    {
        $this->file = $str;
    }

    /**
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function main()
    {
        $config = new Config();
        $config->load($this->file);

        $data = $config->flatten();

        foreach ($data as $name => $value) {
            $this->project->setUserProperty($name, $value);
        }
    }
}