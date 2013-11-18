<?php
namespace watoki\cli;

use watoki\cli\parsers\StandardParser;

class CliApplication {

    /** @var Console */
    private $console;

    /** @var Parser */
    private $parser;

    /** @var bool */
    private $exitOnException = false;

    function __construct(Command $command, Console $console = null, Parser $parser = null) {
        $this->command = $command;

        $this->console = $console ? : new Console();
        $this->parser = $parser ? : new StandardParser();
    }

    /**
     * @param boolean $exitOnException If true, application with exit($exceptionCode) upon exception
     */
    public function setExitOnException($exitOnException = true) {
        $this->exitOnException = $exitOnException;
    }

    /**
     * Parses input arguments and executes the Command
     *
     * @throws \Exception If exitOnException is set false
     */
    public function run() {
        try {
            $this->command->execute($this->console, $this->parser->getArguments());
        } catch (\Exception $e) {
            $this->writeException($e);
            if ($this->exitOnException) {
                exit($e->getCode() ? : 1);
            }
            throw $e;
        }
    }

    private function writeException(\Exception $e) {
        $this->console->err->writeLine('Error [' . get_class($e) . ']: ' . $e->getMessage());
    }

}
 