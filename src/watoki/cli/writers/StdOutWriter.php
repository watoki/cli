<?php
namespace watoki\cli\writers;
 
use watoki\cli\Writer;

class StdOutWriter implements Writer {

    public function write($string) {
        $f = fopen('php://stdout', 'w');
        fwrite($f, $string);
        fclose($f);
    }

    public function writeLine($string) {
        $this->write($string . PHP_EOL);
    }
}
 