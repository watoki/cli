<?php
namespace spec\watoki\cli;

use spec\watoki\cli\fixtures\CliApplicationFixture;
use watoki\scrut\Specification;

/**
 * @property CliApplicationFixture cli <-
 */
class PrintHelpTest extends Specification {

    function testListCommands() {
        $this->cli->givenTheMultiCommand_WithTheBody('CommandWithDescription', '
            /**
             * A description of that command
             * can be multi-line.
             *
             * Everything after a blank line should not
             * be included.
             */
            function doThat() {}

            /**
             * @param $nothing This is not a desription
             */
            function doThis() {}
        ');
        $this->cli->whenIRunTheSubCommand('help');
        $this->cli->thenTheOutputShouldBe(
            "Available commands: (use \"help <command>\" for details about a command)\n\n" .
            "help -- Prints available commands and their descriptions.\n" .
            "that -- A description of that command can be multi-line.\n" .
            "this");
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
 