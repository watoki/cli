<?php
namespace watoki\cli;

use watoki\cli\writers\StdOutWriter;
use watoki\factory\Factory;
use watoki\factory\filters\DefaultFilterFactory;
use watoki\factory\Injector;

class CliApplication {

    /** @var Writer */
    private $stdWriter;

    /** @var Writer */
    private $errWriter;

    private $exitOnException = false;

    function __construct() {
        $this->stdWriter = new StdOutWriter();
    }

    public function setStandardWriter(Writer $writer) {
        $this->stdWriter = $writer;
    }

    public function setErrorWriter(Writer $writer) {
        $this->errWriter = $writer;
    }

    public function run($argv = null) {
        if ($argv === null) {
            $argv = isset($_SERVER['argv']) ? array_slice($_SERVER['argv'], 1) : array();
        }

        $command = array_shift($argv);

        try {
            $this->execute($command, $argv);
        } catch (\Exception $e) {
            $this->writeException($e);
            if ($this->exitOnException) {
                exit($e->getCode() ? : 1);
            }
            throw $e;
        }
    }

    private function execute($command, $argv) {
        $methodName = 'do' . ucfirst($command);

        if (!method_exists($this, $methodName)) {
            throw new \Exception("Command [$command] not found. Command 'help' lists all commands.");
        }

        $factory = new Factory();
        $injector = new Injector($factory);
        $method = new \ReflectionMethod($this, $methodName);

        $parsed = $this->parseArguments($argv, $this->getAbbreviationMap($method));
        $arguments = $injector->injectMethodArguments($method, $parsed, new DefaultFilterFactory($factory));
        $method->invokeArgs($this, $arguments);
    }

    private function getAbbreviationMap(\ReflectionMethod $method) {
        $map = array();
        foreach ($method->getParameters() as $parameter) {
            $matches = array();
            $found = preg_match('/@param.+\$?' . $parameter->getName() . '\s+\[(\S)\]/', $method->getDocComment(), $matches);
            if ($found) {
                $map[$matches[1]] = $parameter->getName();
            }
        }
        return $map;
    }

    private function parseArguments(array $argv, $abbreviations) {
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
                    if (isset($abbreviations[$last])) {
                        $key = $abbreviations[$last];
                        $lastKey = $hasValue ? null : $key;
                    }

                    foreach ($flags as $flag) {
                        if (isset($abbreviations[$flag])) {
                            $options[$abbreviations[$flag]] = true;
                        }
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

        var_dump($args, $options);
        return array_merge($args, $options);
    }

    public function doHelp($commandName) {

    }

    private function writeException(\Exception $e) {
        $writer = $this->errWriter ? : $this->stdWriter;

        $writer->writeLine('Error [' . get_class($e) . ']: ' . $e->getMessage());
    }

    /**
     * @param boolean $exitOnException If true, application with exit($exceptionCode) upon exception
     */
    public function setExitOnException($exitOnException = true) {
        $this->exitOnException = $exitOnException;
    }

}
 