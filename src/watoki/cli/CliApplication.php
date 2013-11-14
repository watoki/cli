<?php
namespace watoki\cli;

use watoki\cli\parsers\StandardParser;
use watoki\cli\writers\StdOutWriter;
use watoki\factory\Factory;
use watoki\factory\filters\DefaultFilterFactory;
use watoki\factory\Injector;

class CliApplication {

    /** @var Writer */
    private $stdWriter;

    /** @var Writer */
    private $errWriter;

    /** @var Parser */
    private $parser;

    /** @var Factory */
    private $factory;

    private $exitOnException = false;

    function __construct() {
        $this->stdWriter = new StdOutWriter();
        $this->parser = new StandardParser();
        $this->factory = new Factory();
    }

    public function setStandardWriter(Writer $writer) {
        $this->stdWriter = $writer;
    }

    public function setErrorWriter(Writer $writer) {
        $this->errWriter = $writer;
    }

    public function setParser(Parser $parser) {
        $this->parser = $parser;
    }

    public function run() {
        try {
            $this->execute($this->parser->getArguments());
        } catch (\Exception $e) {
            $this->writeException($e);
            if ($this->exitOnException) {
                exit($e->getCode() ? : 1);
            }
            throw $e;
        }
    }

    /**
     * Lists all available commands with description.
     *
     * @param string $commandName
     */
    public function doHelp($commandName) {

    }

    private function execute($arguments) {
        $command = array_shift($arguments);
        $methodName = 'do' . ucfirst($command);

        if (!method_exists($this, $methodName)) {
            throw new \Exception("Command [$command] not found. Command 'help' lists all commands.");
        }

        $injector = new Injector($this->factory);
        $method = new \ReflectionMethod($this, $methodName);

        $resolved = $this->resolveFlags($method, $arguments);
        $arguments = $injector->injectMethodArguments($method, $resolved, new DefaultFilterFactory($this->factory));
        $method->invokeArgs($this, $arguments);
    }

    private function resolveFlags(\ReflectionMethod $method, $arguments) {
        $map = $this->getFlagsMap($method);
        $resolved = array();

        foreach ($arguments as $flag => $value) {
            if (array_key_exists($flag, $map)) {
                $key = $map[$flag];
                if (array_key_exists($key, $arguments)) {
                    $resolved[$key] = array_merge((array) $arguments[$key], (array) $value);
                } else {
                    $resolved[$key] = $value;
                }
            } else {
                $resolved[$flag] = $value;
            }
        }
        return $resolved;
    }

    /**
     * @param \ReflectionMethod $method
     * @return array
     */
    private function getFlagsMap(\ReflectionMethod $method) {
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
 