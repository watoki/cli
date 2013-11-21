<?php
namespace watoki\cli\commands;

use watoki\cli\Command;
use watoki\cli\Console;
use watoki\factory\Factory;
use watoki\factory\filters\DefaultFilterFactory;
use watoki\factory\Injector;

abstract class DefaultCommand implements Command {

    /** @var \watoki\factory\Factory */
    private $factory;

    function __construct(Factory $factory = null) {
        $this->factory = $factory ? : new Factory();
    }

    protected function getExecutionMethodName() {
        return 'doExecute';
    }

    /**
     * @param Console $console
     * @param array $arguments Arguments as produced by the Parser
     * @throws \Exception If the class does not implement a "doExecute" method
     * @return void
     */
    public function execute(Console $console, array $arguments) {
        $injector = new Injector($this->factory);
        $methodName = $this->getExecutionMethodName();

        try {
            $method = new \ReflectionMethod($this, $methodName);
        } catch (\ReflectionException $e) {
            throw new \Exception("Command [" . get_class($this) . "] must implement the method [$methodName].");
        }

        $this->factory->setSingleton(Console::$CLASS, $console);

        $resolved = $this->resolveFlags($method, $arguments);
        $arguments = $injector->injectMethodArguments($method, $resolved, new DefaultFilterFactory($this->factory));

        $this->checkArguments($method, $resolved);

        $method->invokeArgs($this, $arguments);
    }

    private function checkArguments(\ReflectionMethod $method, array $arguments) {
        $parameters = array();
        foreach ($method->getParameters() as $parameter) {
            $parameters[$parameter->getPosition()] = $parameter->getName();
        }

        foreach ($arguments as $key => $value) {
            if (!array_key_exists($key, $parameters) && !in_array($key, $parameters)) {
                if (is_numeric($key)) {
                    throw new \Exception('Invalid argument: ' . $value);
                } else {
                    throw new \Exception('Invalid option: ' . $key);
                }
            }
        }

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

    /**
     * @return null|string
     */
    public function getDescription() {
        $meta = $this->getCommandMetaData();
        return isset($meta['description']) ? $meta['description'] : null;
    }

    /**
     * @return null|string
     */
    public function getHelpText() {
        $meta = $this->getCommandMetaData();

        $output = '';

        if ($meta['details']) {
            $output .= ' ' . trim(str_replace(PHP_EOL, PHP_EOL . " ", $meta['details']));
        }

        $optionDescriptions = $this->getOptionDescriptions();

        if ($optionDescriptions) {
            $output .= PHP_EOL;
            $output .= 'Valid options:' . PHP_EOL;
        }

        foreach ($optionDescriptions as $option) {
            $output .= ' ' . $option . PHP_EOL;
        }

        return trim($output, PHP_EOL);
    }

    private function getCommandMetaData() {
        $method = new \ReflectionMethod($this, $this->getExecutionMethodName());
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
                    $meta['description'] = str_replace(array("\r\n", "\r", "\n"), ' ', $accumulator);
                } else if (!$meta['details']) {
                    $meta['details'] = $accumulator;
                }
                $accumulator = '';
            }

            if (substr($line, 0, 1) == '@') {
                break;
            } else {
                $accumulator = trim($accumulator . PHP_EOL . $line);
            }
        }

        return $meta;
    }

    private function getOptionDescriptions() {
        $method = new \ReflectionMethod($this, $this->getExecutionMethodName());
        $options = array();
        foreach ($method->getParameters() as $parameter) {
            $matches = array();
            $found = preg_match('/@param\s+(\S*\s+)?\$?' . $parameter->getName() . '[ \t]*(\[\S?\])?([^\n]*)/', $method->getDocComment(),
                $matches);

            if (!$found || $matches[2] == '[]') {
                continue;
            }

            $type = trim($matches[1]);
            $flag = trim($matches[2], '[]');
            $description = trim($matches[3]);

            if ($parameter->getClass()) {
                $type = $parameter->getClass()->getName();
            }

            if ($parameter->isDefaultValueAvailable()) {
                $type .= '=' . var_export($parameter->getDefaultValue(), true);
            }

            $option = '--' . $parameter->getName();

            if ($flag) {
                $option .= '|-' . $flag;
            }

            if ($type || $description) {
                $option .= ':' . ($type ? ' (' . $type . ')' : '') . ($description ? ' ' . $description : '');
            }

            $options[] = $option;
        }
        return $options;
    }
}
 