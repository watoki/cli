<?php
namespace spec\watoki\cli;

use spec\watoki\cli\fixtures\CliApplicationFixture;
use watoki\scrut\Specification;

/**
 * @property CliApplicationFixture cli <-
 */
class PrintHelpTest extends Specification {

    function testDefaultBehaviour() {
        $this->cli->whenRunTheApplication();
        $this->cli->thenTheOutputShouldBe(
            "Available commands: (use \"help <command>\" for details)" . PHP_EOL . PHP_EOL .
            "help -- Prints available commands and their descriptions.");
    }

    function testListCommands() {
        $this->cli->givenTheCommand_WithTheBody('CommandWithDescription1', '
            /**
             * A description of that command
             * can be multi-line.
             *
             * Everything after a blank line should not
             * be included.
             */
            function doExecute() {}
        ');
        $this->cli->givenTheCommand_WithTheBody('CommandWithDescription2', '
            /**
             * @param $nothing This is not a description
             */
            function doExecute() {}
        ');
        $this->cli->whenIRunTheCommand('help');
        $this->cli->thenTheOutputShouldBe(
            "Available commands: (use \"help <command>\" for details)" . PHP_EOL . PHP_EOL .
            "help -- Prints available commands and their descriptions." . PHP_EOL .
            "CommandWithDescription1 -- A description of that command can be multi-line." . PHP_EOL .
            "CommandWithDescription2");
    }

    function testCommandDetailsWithDescription() {
        $this->cli->givenTheCommand_WithTheBody('CommandDetailsWithDescription', '
            /**
             * The description of that command.
             *
             * Everything after a blank line is
             * considered detailed description.
             */
            function doExecute() {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('CommandDetailsWithDescription'));
        $this->cli->thenTheOutputShouldBe(
            "CommandDetailsWithDescription: The description of that command." . PHP_EOL . PHP_EOL .
            " Everything after a blank line is" . PHP_EOL .
            " considered detailed description.");
    }

    function testCommandDetailsWithoutDescription() {
        $this->cli->givenTheCommand_WithTheBody('CommandDetailsWithoutDescription', '
            function doExecute() {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('CommandDetailsWithoutDescription'));
        $this->cli->thenTheOutputShouldBe("CommandDetailsWithoutDescription: (No description available)");
    }

    function testOptionsWithoutDocComment() {
        $this->cli->givenTheCommand_WithTheBody('OptionsWithoutDocComment', '
            function doExecute($one, $two, $three) {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('OptionsWithoutDocComment'));
        $this->cli->thenTheOutputShouldBe(
            "OptionsWithoutDocComment: (No description available)");
    }

    function testOptionsWithoutDescriptions() {
        $this->cli->givenTheCommand_WithTheBody('OptionsWithoutDescriptions', '
            /**
             * @param $one
             * @param $two
             * @param $three
             */
            function doExecute($one, $two, $three) {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('OptionsWithoutDescriptions'));
        $this->cli->thenTheOutputShouldBe(
            "OptionsWithoutDescriptions: (No description available)" . PHP_EOL . PHP_EOL .
            "Valid options:" . PHP_EOL .
            " --one" . PHP_EOL .
            " --two" . PHP_EOL .
            " --three");
    }

    function testOptionsWithDescriptionsAndTypes() {
        $this->cli->givenTheCommand_WithTheBody('OptionsWithDescriptionsAndTypes', '
            /**
             * @param $one Just a description
             * @param boolean $two Description with type
             * @param $three Class type hint
             */
            function doExecute($one, $two, \DateTime $three) {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('OptionsWithDescriptionsAndTypes'));
        $this->cli->thenTheOutputShouldBe(
            "OptionsWithDescriptionsAndTypes: (No description available)" . PHP_EOL . PHP_EOL .
            "Valid options:" . PHP_EOL .
            " --one: Just a description" . PHP_EOL .
            " --two: (boolean) Description with type" . PHP_EOL .
            " --three: (DateTime) Class type hint");
    }

    function testDefaultOptions() {
        $this->cli->givenTheCommand_WithTheBody('DefaultOptions', '
            /**
             * @param $one
             * @param boolean $two A description
             */
            function doExecute($one=null, $two=false) {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('DefaultOptions'));
        $this->cli->thenTheOutputShouldBe(
            "DefaultOptions: (No description available)" . PHP_EOL . PHP_EOL .
            "Valid options:" . PHP_EOL .
            " --one: (=NULL)" . PHP_EOL .
            " --two: (boolean=false) A description");
    }

    function testFlagOptions() {
        $this->cli->givenTheCommand_WithTheBody('FlagOptions', '
            /**
             * @param $one [o] A description
             */
            function doExecute($one) {}
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('help', array('FlagOptions'));
        $this->cli->thenTheOutputShouldBe(
            "FlagOptions: (No description available)" . PHP_EOL . PHP_EOL .
            "Valid options:" . PHP_EOL .
            " --one|-o: A description");
    }

}
 