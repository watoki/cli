<?php
namespace watoki\cli\commands;
 
use watoki\cli\Command;
use watoki\cli\Console;

class DependentCommandGroup extends CommandGroup {

    private $dependencies = array();

    private $queue = array();

    /**
     * @param string $from Name of the depending command
     * @param string $to Name of the dependency command
     * @param array $arguments Arguments to invoke the dependency with
     */
    public function addDependency($from, $to, $arguments = array()) {
        $this->dependencies[$from][] = array('command' => $to, 'arguments' => $arguments);
    }

    public function execute(Console $console, array $arguments) {
        $this->queue = array();
        parent::execute($console, $arguments);
    }

    protected function executeCommand($name, array $arguments, Console $console) {
        $fingerprint = $this->fingerprint($name, $arguments);
        if (array_key_exists($fingerprint, $this->queue)) {
            return;
        }

        foreach ($this->dependencies[$name] as $dependency) {
            $this->executeCommand($dependency['command'], $dependency['arguments'], $console);
        }

        $this->queue[$fingerprint] = false;
        parent::executeCommand($name, $arguments, $console);
        $this->queue[$fingerprint] = true;
    }

    private function fingerprint($name, $arguments) {
        return json_encode(array($name, $arguments));
    }

    public function add($name, Command $command) {
        parent::add($name, $command);
        $this->dependencies[$name] = array();
    }

}
 