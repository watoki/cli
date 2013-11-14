<?php
namespace watoki\cli\parsers;
 
use watoki\cli\Parser;

class StandardParser implements Parser {

    protected function readArguments() {
        return array_slice($_SERVER['argv'], 1);
    }

    public function getArguments() {
        $argv = $this->readArguments();

        $args = array();
        $options = array();

        $lastKey = null;
        for ($i = 0, $c = count($argv); $i < $c; $i++) {
            $arg = $argv[$i];
            if ($arg === '--') {
                $args[] = implode(' ', array_slice($argv, $i + 1));
                break;
            }

            if (substr($arg, 0, 1) == '-' || $lastKey) {
                $key = $arg;
                $value = true;
                $hasValue = false;

                if (($sep = strpos($arg, '=')) !== false) {
                    $key = substr($arg, 0, $sep);
                    $value = substr($arg, $sep + 1);
                    $hasValue = true;
                }

                if (substr($key, 0, 2) === '--') {
                    $key = substr($key, 2);
                    $lastKey = $hasValue ? null : $key;
                } else if (substr($key, 0, 1) === '-') {
                    $flags = str_split(substr($key, 1));

                    $last = array_pop($flags);
                    $key = $last;
                    $lastKey = $hasValue ? null : $key;

                    foreach ($flags as $flag) {
                        $options[$flag] = true;
                    }
                } else {
                    $value = $arg;
                    $key = $lastKey;
                    $lastKey = null;

                    if (is_array($options[$key])) {
                        array_pop($options[$key]);
                    } else {
                        unset($options[$key]);
                    }
                }

                if (array_key_exists($key, $options)) {
                    if (!is_array($options[$key])) {
                        $options[$key] = array($options[$key]);
                    }
                    $options[$key][] = $value;
                } else {
                    $options[$key] = $value;
                }
            } else {
                $args[] = $arg;
                $lastKey = null;
            }
        }

        return array_merge($args, $options);
    }
}
 