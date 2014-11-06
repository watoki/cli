<?php
namespace watoki\cli\commands;

use watoki\cli\Command;
use watoki\cli\Console;

class CommandGroup implements Command {

    /** @var array|Command[] */
    private $commands = array();

    private $description;

    private $helpText;

    function __construct($commands = array()) {
        $this->commands = $commands;

        $that = $this;
        $this->add('help', GenericCommand::build(function (Console $console, array $arguments) use ($that) {
            if (!empty($arguments)) {
                $commandName = $arguments[0];

                foreach ($that->getCommands() as $name => $command) {
                    if ($commandName == $name) {
                        $description = $command->getDescription();

                        $console->out->writeLine($name . ': ' . ($description ? $description : '(No description available)'));

                        $helpText = $command->getHelpText();
                        if ($helpText) {
                            $console->out->writeLine('');
                            $console->out->writeLine($helpText);
                        }
                        return;
                    }
                }

                $console->out->writeLine("Command [$commandName] not found.");
                $console->out->writeLine('');
            }

            $console->out->writeLine('Available commands: (use "help <command>" for details)');
            $console->out->writeLine('');

            foreach ($that->getCommands() as $name => $command) {
                $description = $command->getDescription();
                $console->out->writeLine($name . ($description ? ' -- ' . $description : ''));
            }
        })->setDescription('Prints available commands and their descriptions.'));
    }

    /**
     * @param string $name
     * @param Command $command
     */
    public function add($name, Command $command) {
        $this->commands[$name] = $command;
    }

    /**
     * @return Command[]
     */
    public function getCommands() {
        return $this->commands;
    }

    /**
     * @param Console $console
     * @param array $arguments Arguments as produced by the Parser
     * @throws \Exception
     * @return void
     */
    public function execute(Console $console, array $arguments) {
        if (empty($arguments)) {
            $commandName = 'help';
        } else {
            $commandName = array_shift($arguments);
        }

        if (!array_key_exists($commandName, $this->commands)) {
            throw new \Exception("Command [$commandName] not found. Use 'help' to list available commands.");
        }

        $this->executeCommand($commandName, $arguments, $console);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @param Console $console
     */
    protected function executeCommand($name, array $arguments, Console $console) {
        $this->commands[$name]->execute($console, $arguments);
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
     * @return $this
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @param mixed $helpText
     * @return $this
     */
    public function setHelpText($helpText) {
        $this->helpText = $helpText;
        return $this;
    }
}
 