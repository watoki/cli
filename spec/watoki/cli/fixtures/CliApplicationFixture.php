<?php
namespace spec\watoki\cli\fixtures;
 
use watoki\cli\CliApplication;
use watoki\cli\commands\CommandGroup;
use watoki\cli\commands\DependentCommandGroup;
use watoki\cli\Console;
use watoki\cli\writers\ArrayWriter;
use watoki\scrut\Fixture;

class CliApplicationFixture extends Fixture {

    protected $command;

    /** @var ArrayWriter */
    private $writer;

    /** @var null|\Exception */
    private $caught;

    /** @var CommandGroup|DependentCommandGroup */
    private $commandGroup;

    private $parser;

    public function setUp() {
        $this->writer = new ArrayWriter();
        $this->commandGroup = new CommandGroup();
        $this->parser = $this->createParser();
    }

    public function givenADependentCommandGroup() {
        $this->commandGroup = new DependentCommandGroup();
        $this->commandGroup->setVerbosity(false);
    }

    public function givenTheCommand_DependsOn($name, $command) {
        $this->givenTheCommand_DependsOn_WithTheArguments($name, $command, array());
    }

    public function givenTheCommand_DependsOn_WithTheArguments($name, $command, $args) {
        $this->commandGroup->addDependency($name, $command, $args);
    }

    public function givenTheCommand($name) {
        $this->givenTheCommand_WithTheBody($name, '
            function doExecute(\watoki\cli\Console $c) {
                $c->out->writeLine("X:' . $name . '");
            }
        ');
    }

    public function givenTheCommand_WithTheBody($name, $body) {
        eval("class $name extends \\watoki\\cli\\commands\\DefaultCommand {
            $body
        }");
        $this->command = new $name;
        $this->commandGroup->add($name, $this->command);
    }

    public function whenTryToIRunTheCommand($command) {
        $this->whenTryToIRunTheCommand_WithTheArguments($command, array());
    }

    public function whenTryToIRunTheCommand_WithTheArguments($command, $args) {
        try {
            $this->whenIRunTheCommand_WithTheArguments($command, $args);
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function whenIRunTheCommand($command) {
        $this->whenIRunTheCommand_WithTheArguments($command, array());
    }

    public function whenIRunTheCommand_WithTheArguments($command, $args) {
        $this->parser->arguments = array_merge(array($command), $args);
        $this->whenRunTheApplication();
    }

    public function whenRunTheApplication() {
        $app = new CliApplication($this->commandGroup, new Console($this->writer), $this->parser);
        $app->run();
    }

    public function thenThereShouldBeAnErrorContaining($string) {
        $this->spec->assertNotNull($this->caught, "No Exception caught");
        $this->spec->assertContains($string, $this->caught->getMessage());
    }

    public function then_ShouldBe($field, $value) {
        $this->spec->assertEquals($value, $this->command->$field);
    }

    public function then_ShouldBeExactly($field, $value) {
        $this->spec->assertSame($value, $this->command->$field);
    }

    public function thenTheOutputShouldBe($string) {
        $this->spec->assertSame($string, implode(PHP_EOL, $this->writer->getOutput()));
    }

    private function createParser() {
        $parserClass = 'MyParser';
        if (!class_exists($parserClass)) {
            eval("class $parserClass extends \\watoki\\cli\\parsers\\StandardParser {
                public \$arguments = array();

                protected function readArguments() {
                    return \$this->arguments;
                }

                public function getName() {
                    return 'cli.php';
                }
            }");
        }
        return new $parserClass;
    }
}
 