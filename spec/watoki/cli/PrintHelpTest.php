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
            "that: The description of that command.\n\n" .
            " Everything after a blank line is\n" .
            " considered detailed description.");
    }

    function testCommandDetailsWithoutDescription() {
        $this->cli->givenTheMultiCommand_WithTheBody('CommandDetailsWithoutDescription', '
            function doThat() {}
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('help', array('that'));
        $this->cli->thenTheOutputShouldBe("that: (No description available)");
    }

    function testOptionsWithoutDescriptions() {
        $this->cli->givenTheMultiCommand_WithTheBody('OptionsWithoutDescriptions', '
            function doThat($one, $two, $three) {}
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('help', array('that'));
        $this->cli->thenTheOutputShouldBe(
            "that: (No description available)\n\n" .
            "Valid options:\n" .
            " --one\n" .
            " --two\n" .
            " --three");
    }

    function testOptionsWithDescriptionsAndTypes() {
        $this->cli->givenTheMultiCommand_WithTheBody('OptionsWithDescriptionsAndTypes', '
            /**
             * @param $one Just a description
             * @param boolean $two Description with type
             * @param $three Class type hint
             */
            function doThat($one, $two, \DateTime $three) {}
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('help', array('that'));
        $this->cli->thenTheOutputShouldBe(
            "that: (No description available)\n\n" .
            "Valid options:\n" .
            " --one: Just a description\n" .
            " --two: (boolean) Description with type\n" .
            " --three: (DateTime) Class type hint");
    }

    function testDefaultOptions() {
        $this->cli->givenTheMultiCommand_WithTheBody('DefaultOptions', '
            /**
             * @param boolean $two A description
             */
            function doThat($one=null, $two=false) {}
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('help', array('that'));
        $this->cli->thenTheOutputShouldBe(
            "that: (No description available)\n\n" .
            "Valid options:\n" .
            " --one: (=NULL)\n" .
            " --two: (boolean=false) A description");
    }

    function testFlagOptions() {
        $this->markTestIncomplete();
    }

}
 