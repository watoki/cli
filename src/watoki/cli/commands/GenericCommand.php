<?php
namespace watoki\cli\commands;

use watoki\cli\Command;
use watoki\cli\Console;

class GenericCommand implements Command {

    /** @var callable */
    private $callback;

    private $description;

    private $helpText;

    /**
     * @param callable $callback
     */
    function __construct($callback) {
        $this->callback = $callback;
    }

    /**
     * @param callable $callback
     * @return GenericCommand
     */
    public static function build($callback) {
        return new GenericCommand($callback);
    }

    /**
     * @param Console $console
     * @param array $arguments Arguments as produced by the Parser
     * @return void
     */
    public function execute(Console $console, array $arguments) {
        $callback = $this->callback;
        $callback($console, $arguments);
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return null|string
     */
    public function getHelpText() {
        return $this->helpText;
    }

    /**
     * @param mixed $description
     * @return self
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param mixed $helpText
     * @return self
     */
    public function setHelpText($helpText) {
        $this->helpText = $helpText;
        return $this;
    }
}
 