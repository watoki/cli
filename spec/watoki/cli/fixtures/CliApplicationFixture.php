<?php
namespace spec\watoki\cli\fixtures;
 
use watoki\cli\CliApplication;
use watoki\cli\Parser;
use watoki\cli\writers\ArrayWriter;
use watoki\scrut\Fixture;

class CliApplicationFixture extends Fixture {

    /** @var CliApplication */
    private $app;

    /** @var ArrayWriter */
    private $writer;

    private $applicationName = 'watoki\cli\CliApplication';

    /** @var null|\Exception */
    private $caught;

    /** @var Parser */
    private $parser;

    public function givenTheApplication_WithTheBody($className, $body) {
        eval("class $className extends \\watoki\\cli\\CliApplication {
            $body
        }");
        $this->applicationName = $className;
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
            }");
        }
        $this->parser = new $parserClass;
    }

    public function whenTryToIRunTheCommand($command) {
        try {
            $this->whenIRunTheCommand_WithTheArguments($command, array());
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function whenIRunTheCommand($command) {
        $this->whenIRunTheCommand_WithTheArguments($command, array());
    }

    public function whenIRunTheCommand_WithTheArguments($command, $args) {
        $applicationName = $this->applicationName;
        $this->parser->arguments = array_merge(array($command), $args);

        $this->app = new $applicationName();
        $this->app->setStandardWriter($this->writer);
        $this->app->setParser($this->parser);
        $this->app->run();
    }

    public function thenThereShouldBeAnErrorContaining($string) {
        $this->spec->assertContains($string, $this->caught->getMessage());
    }

    public function then_ShouldBe($field, $value) {
        $this->spec->assertEquals($value, $this->app->$field);
    }

    public function then_ShouldBeExactly($field, $value) {
        $this->spec->assertSame($value, $this->app->$field);
    }
}
 