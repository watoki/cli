<?php
namespace watoki\cli;

use watoki\cli\parsers\StandardParser;
use watoki\cli\writers\StdOutWriter;

class CliApplication {

    /** @var Writer */
    private $stdWriter;

    /** @var Writer */
    private $errWriter;

    /** @var Parser */
    private $parser;

    private $exitOnException = false;

    function __construct(Command $command) {
        $this->command = $command;

        $this->stdWriter = new StdOutWriter();
        $this->parser = new StandardParser();
    }

    public function setStandardWriter(Writer $writer) {
        $this->stdWriter = $writer;
    }

    /**
     * @return \watoki\cli\Writer
     */
    public function getStdWriter() {
        return $this->stdWriter;
    }

    public function setErrorWriter(Writer $writer) {
        $this->errWriter = $writer;
    }

    /**
     * @return \watoki\cli\Writer
     */
    public function getErrWriter() {
        return $this->errWriter;
    }

    public function setParser(Parser $parser) {
        $this->parser = $parser;
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
            $this->command->execute($this, $this->parser->getArguments());
        } catch (\Exception $e) {
            $this->writeException($e);
            if ($this->exitOnException) {
                exit($e->getCode() ? : 1);
            }
            throw $e;
        }
    }

    private function writeException(\Exception $e) {
        $writer = $this->errWriter ? : $this->stdWriter;

        $writer->writeLine('Error [' . get_class($e) . ']: ' . $e->getMessage());
    }

}
 