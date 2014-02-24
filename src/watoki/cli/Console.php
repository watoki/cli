<?php
namespace watoki\cli;
 
use watoki\cli\readers\StdInReader;
use watoki\cli\writers\StdOutWriter;

class Console {

    public static $CLASS = __CLASS__;

    /** @var Writer */
    public $out;

    /** @var Writer */
    public $err;

    /** @var Reader */
    public $in;

    function __construct(Writer $out = null, Reader $in = null, Writer $err = null) {
        $this->out = $out ? : new StdOutWriter();
        $this->in = $in ? : new StdInReader();
        $this->err = $err ? : $this->out;
    }

    function ask($question) {
        $this->out->write($question);
        return trim($this->in->read());
    }

}
 