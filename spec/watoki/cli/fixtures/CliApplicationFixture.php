<?php
namespace spec\watoki\cli\fixtures;
 
use watoki\cli\CliApplication;
use watoki\cli\Parser;
use watoki\cli\writers\ArrayWriter;
use watoki\scrut\Fixture;

class CliApplicationFixture extends Fixture {

    protected $command;

    /** @var ArrayWriter */
    private $writer;

    private $commandName = 'watoki\cli\commands\MultiCommand';

    /** @var null|\Exception */
    private $caught;

    /** @var Parser */
    private $parser;

    public function givenTheMultiCommand_WithTheBody($className, $body) {
        eval("class $className extends \\watoki\\cli\\commands\\MultiCommand {
            $body
        }");
        $this->commandName = $className;
    }

    protected function setUp() {
        $this->writer = new ArrayWriter();

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
        $this->parser = new $parserClass;
    }

    public function whenTryToIRunTheSubCommand($command) {
        try {
            $this->whenIRunTheSubCommand_WithTheArguments($command, array());
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function whenIRunTheSubCommand($command) {
        $this->whenIRunTheSubCommand_WithTheArguments($command, array());
    }

    public function whenIRunTheSubCommand_WithTheArguments($command, $args) {
        $commandName = $this->commandName;

        $this->command = new $commandName;
        $app = new CliApplication($this->command);
        $app->setStandardWriter($this->writer);

        $this->parser->arguments = array_merge(array($command), $args);
        $app->setParser($this->parser);

        $app->run();
    }

    public function thenThereShouldBeAnErrorContaining($string) {
        $this->spec->assertContains($string, $this->caught->getMessage());
    }

    public function then_ShouldBe($field, $value) {
        $this->spec->assertEquals($value, $this->command->$field);
    }

    public function then_ShouldBeExactly($field, $value) {
        $this->spec->assertSame($value, $this->command->$field);
    }

    public function thenTheOutputShouldBe($string) {
        $this->spec->assertSame($string, implode("\n", $this->writer->getOutput()));
    }
}
 