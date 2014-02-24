<?php
namespace watoki\cli\readers;

use watoki\cli\Reader;

class StdInReader implements Reader {

    public function read() {
        $handle = fopen("php://stdin", "r");
        return fgets($handle);
    }
}
 