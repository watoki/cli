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
            $method = new \ReflectionMethod($this->object, 'do' . ucfirst($commandName));
            $meta = $this->getCommandMetaData($method);

            if (!$meta['description']) {
                $this->writeLine('(No description available)');
            } else {
                $this->writeLine($meta['description']);
            }
            
            if ($meta['details']) {
                $this->writeLine('');
                $this->writeLine($meta['details']);
            }
        } catch (\ReflectionException $e) {
            throw new \Exception("Command [$commandName] does not exist.", 0, $e);
        }
    }

    public function listCommands() {
        $this->writeLine('Available commands: (use "help <command>" for details about a command)');
        $this->writeLine('');

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
            $meta = $this->getCommandMetaData($method);
            $this->writeLine($name . ($meta['description'] ? ' -- ' . $meta['description'] : ''));
        }
    }

    private function getCommandMetaData(\ReflectionMethod $method) {
        $comment = $method->getDocComment();

        $meta = array(
            'description' => '',
            'details' => ''
        );

        $accumulator = '';
        foreach (explode("\n", $comment) as $line) {
            $line = trim(str_replace(array('/*', '/**', '*/', '*'), '', $line));

            if (!$line && $accumulator) {
                if (!$meta['description']) {
                    $meta['description'] = $accumulator;
                } else if (!$meta['details']) {
                    $meta['details'] = $accumulator;
                }
                $accumulator = '';
            }

            if (substr($line, 0, 1) == '@') {
                $accumulator = '';
            } else {
                $accumulator = trim($accumulator . ' ' . $line);
            }
        }

        return $meta;
    }

    private function writeLine($string) {
        $this->app->getStdWriter()->writeLine($string);
    }

}
 