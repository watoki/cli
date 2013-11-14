<?php
namespace watoki\cli\writers;

use watoki\cli\Writer;

class ArrayWriter implements Writer {

    private $output = array('');

    /**
     * @return array
     */
    public function getOutput() {
        if ($this->output[count($this->output) - 1] == '') {
            return array_slice($this->output, 0, count($this->output) - 1);
        }
        return $this->output;
    }

    public function write($string) {
        $this->output[count($this->output) - 1] .= $string;
    }

    public function writeLine($string) {
        $this->write($string);
        $this->output[] = '';
    }
}
 