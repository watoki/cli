<?php
namespace watoki\cli\commands;
 
use watoki\cli\CliApplication;

class HelpPrinter {

    /** @var \watoki\cli\CliApplication */
    private $app;

    /** @var object */
    private $object;

    function __construct(CliApplication $app, $object) {
        $this->app = $app;
        $this->object = $object;
    }

    /**
     * @param $commandName
     * @throws \Exception
     */
    public function printHelpForCommand($commandName) {
        try {
            new \ReflectionMethod($this->object, 'do' . ucfirst($commandName));
        } catch (\ReflectionException $e) {
            throw new \Exception("Command [$commandName] does not exist.", 0, $e);
        }
    }

    public function listCommands() {
        $this->app->getStdWriter()->writeLine('Available commands: (use "help <command>" for details about a command)');
        $this->app->getStdWriter()->writeLine('');

        $reflection = new \ReflectionClass($this->object);
        $commands = array();
        foreach ($reflection->getMethods() as $method) {
            if (substr($method->getName(), 0, 2) != 'do') {
                continue;
            }

            $commands[lcfirst(substr($method->getName(), 2))] = $method;
        }

        ksort($commands);

        foreach ($commands as $name => $method) {
            $this->app->getStdWriter()->writeLine($name . ' -- ');
        }
    }

}
 