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
                $this->writeLine($commandName . ': (No description available)');
            } else {
                $this->writeLine($commandName . ': ' . $meta['description']);
            }
            
            if ($meta['details']) {
                $this->writeLine('');
                $this->writeLine(' ' . trim(str_replace("\n", "\n ", $meta['details'])));
            }

            $optionDescriptions = $this->getOptionDescriptions($method);

            if ($optionDescriptions) {
                $this->writeLine('');
                $this->writeLine('Valid options:');
            }

            foreach ($optionDescriptions as $option) {
                $this->writeLine(' ' . $option);
            }
        } catch (\ReflectionException $e) {
            throw new \Exception("Command [$commandName] does not exist.", 0, $e);
        }
    }

    public function listCommands() {
        $this->writeLine('Available commands: (use "php ' . $this->app->getName() . ' help <command>" for details about a command)');
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
                    $meta['description'] = str_replace("\n", ' ', $accumulator);
                } else if (!$meta['details']) {
                    $meta['details'] = $accumulator;
                }
                $accumulator = '';
            }

            if (substr($line, 0, 1) == '@') {
                break;
            } else {
                $accumulator = trim($accumulator . "\n" . $line);
            }
        }

        return $meta;
    }

    private function getOptionDescriptions(\ReflectionMethod $method) {
        $options = array();
        foreach ($method->getParameters() as $parameter) {
            $type = '';
            $description = '';

            $matches = array();
            $found = preg_match('/@param\s+(\S*\s+)?\$?' . $parameter->getName() .'\s*([^\n]*)/', $method->getDocComment(), $matches);
            if ($found) {
                $type = trim($matches[1]);
                $description = trim($matches[2]);
            }

            if ($parameter->getClass()) {
                $type = $parameter->getClass()->getName();
            }

            $option = '--' . $parameter->getName();
            if ($type || $description) {
                $option .= ':' . ($type ? ' (' . $type . ')' : '') . ' ' . $description;
            }

            $options[] = $option;
        }
        return $options;
    }

    private function writeLine($string) {
        $this->app->getStdWriter()->writeLine($string);
    }

}
 