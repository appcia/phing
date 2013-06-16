<?php

require_once "vendor/phing/phing/classes/phing/Task.php";
require_once "vendor/appcia/webwork/lib/Appcia/Webwork/Storage/Config.php";

use Appcia\Webwork\Storage\Config;

class PropertyArrayTask extends Task {

    /**
     * @var string
     */
    private $file;

    /**
     * @param string $str
     */
    public function setFile($str) {
        $this->file = $str;
    }

    /**
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function main() {
        if (empty($this->file)) {
            throw new \InvalidArgumentException("Property 'file' must be specified.");
        }

        if (!file_exists($this->file)) {
            throw new \LogicException(sprintf("Property array file '%s' does not exist.", $this->file));
        }

        $config = new Config();
        $config->loadFile($this->file);

        $data = $config->flatten();

        foreach ($data as $name => $value) {
            $this->project->setProperty($name, $value);
        }
    }
}