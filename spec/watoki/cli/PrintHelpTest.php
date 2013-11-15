<?php
namespace spec\watoki\cli;

use spec\watoki\cli\fixtures\CliApplicationFixture;
use watoki\scrut\Specification;

/**
 * @property CliApplicationFixture cli <-
 */
class PrintHelpTest extends Specification {

    function testListCommands() {
        $this->markTestIncomplete();
        $this->cli->givenTheMultiCommand_WithTheBody('CommandWithDescription', '
            /**
             * Description of that command
             */
            function doThat() {}
        ');
        $this->cli->whenIRunTheSubCommand('help');
        $this->cli->thenTheOutputShouldBe(
            "Available commands: (use \"help <command>\" for details about a command)\n\n" .
            "help -- Prints available commands and their usage.\n" .
            "that -- Description of that command");
    }

    function testCommandDetails() {
        $this->markTestIncomplete();
    }

    function testFlagOptions() {
        $this->markTestIncomplete();
    }

    function testDefaultOptions() {
        $this->markTestIncomplete();
    }

}
 