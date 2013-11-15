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
             * @param $nothing This is not a description
             */
            function doThis() {}
        ');
        $this->cli->whenIRunTheSubCommand('help');
        $this->cli->thenTheOutputShouldBe(
            "Available commands: (use \"php cli.php help <command>\" for details about a command)\n\n" .
            "help -- Prints available commands and their descriptions.\n" .
            "that -- A description of that command can be multi-line.\n" .
            "this");
    }

    function testCommandDetailsWithDescription() {
        $this->cli->givenTheMultiCommand_WithTheBody('CommandDetailsWithDescription', '
            /**
             * The description of that command.
             *
             * Everything after a blank line is
             * considered detailed description.
             */
            function doThat() {}
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('help', array('that'));
        $this->cli->thenTheOutputShouldBe(
            "The description of that command.\n\n" .
            "Everything after a blank line is considered detailed description.");
    }

    function testCommandDetailsWithoutDescription() {
        $this->cli->givenTheMultiCommand_WithTheBody('CommandDetailsWithoutDescription', '
            function doThat() {}
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('help', array('that'));
        $this->cli->thenTheOutputShouldBe("(No description available)");
    }

    function testCommandDetailsWithOptions() {
        $this->markTestIncomplete();
    }

    function testFlagOptions() {
        $this->markTestIncomplete();
    }

    function testDefaultOptions() {
        $this->markTestIncomplete();
    }

}
 