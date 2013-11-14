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
        return array();
    }

    private function parseArguments(array $argv, $abbreviations) {
        $args = array();
        $options = array();

        for ($i = 0, $c = count($argv); $i < $c; $i++) {
            $arg = $argv[$i];
            if ($arg === '--') {
                $args[] = implode(' ', array_slice($argv, $i + 1));
                break;
            }
            if (substr($arg, 0, 2) === '--') {
                $key = substr($arg, 2);
                $value = true;
                if (($sep = strpos($arg, '=')) !== false) {
                    $key = substr($arg, 2, $sep - 2);
                    $value = substr($arg, $sep + 1);
                }
                if (array_key_exists($key, $options)) {
                    if (!is_array($options[$key])) {
                        $options[$key] = array($options[$key]);
                    }
                    $options[$key][] = $value;
                } else {
                    $options[$key] = $value;
                }
            } else if (substr($arg, 0, 1) === '-') {
                foreach (str_split(substr($arg, 1)) as $key) {
                    if (isset($abbreviations[$key])) {
                        $options[$abbreviations[$key]] = true;
                    }
                }
            } else {
                $args[] = $arg;
            }
        }

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
 