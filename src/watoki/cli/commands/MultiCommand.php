<?php
namespace watoki\cli\commands;
use watoki\cli\CliApplication;
use watoki\cli\Command;
use watoki\factory\Factory;
use watoki\factory\filters\DefaultFilterFactory;
use watoki\factory\Injector;

class MultiCommand implements Command {

    /** @var CliApplication */
    protected $app;

    /** @var \watoki\factory\Factory */
    private $factory;

    function __construct() {
        $this->factory = new Factory();
    }

    /**
     * Lists all available commands with description or help for single command.
     *
     * @param string $commandName Name of command to display description and arguments of
     * @throws \Exception If the given command does not exist
     */
    public function doHelp($commandName = null) {
        $help = new HelpPrinter($this->app, $this);
        if ($commandName) {
            $help->printHelpForCommand($commandName);
        } else {
            $help->listCommands();
        }
    }

    public function execute(CliApplication $app, array $arguments) {
        $this->app = $app;

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

}
 