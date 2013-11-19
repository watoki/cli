<?php
namespace watoki\cli\commands;
 
use watoki\cli\Command;
use watoki\cli\Console;

class DependentCommandGroup extends CommandGroup {

    private $dependencies = array();

    private $queue = array();

    private $executed = array();

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
        $this->executed = array();

        parent::execute($console, $arguments);
    }

    protected function executeCommand($name, array $arguments, Console $console) {
        $fingerprint = $this->fingerprint($name, $arguments);
        if (array_key_exists($fingerprint, $this->executed)) {
            return;
        }

        if (array_key_exists($fingerprint, $this->queue)) {
            $circle = '[' . implode('] -> [', array_merge(array_values($this->queue), array($name))) . ']';
            throw new \Exception('Circular dependency detected: ' . $circle);
        }

        $this->queue[$fingerprint] = $name;

        foreach ($this->dependencies[$name] as $dependency) {
            $this->executeCommand($dependency['command'], $dependency['arguments'], $console);
        }

        parent::executeCommand($name, $arguments, $console);

        array_pop($this->queue);
        $this->executed[$fingerprint] = true;
    }

    private function fingerprint($name, $arguments) {
        return json_encode(array($name, $arguments));
    }

    public function add($name, Command $command) {
        parent::add($name, $command);
        $this->dependencies[$name] = array();
    }

}
 