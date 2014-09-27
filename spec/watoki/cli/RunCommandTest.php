<?php
namespace spec\watoki\cli;

use spec\watoki\cli\fixtures\CliApplicationFixture;
use watoki\scrut\Specification;

/**
 * @property CliApplicationFixture cli <-
 */
class RunCommandTest extends Specification {

    function testNonExistingCommand() {
        $this->cli->whenTryToIRunTheCommand('nonExisting');
        $this->cli->thenThereShouldBeAnErrorContaining("Command [nonExisting] not found. Use 'help' to list available commands.");
    }

    function testWithoutArgs() {
        $this->cli->givenTheCommand_WithTheBody('CommandWithoutArgs', '
            function doExecute() {
                $this->executed = true;
            }
        ');
        $this->cli->whenIRunTheCommand('CommandWithoutArgs');

        $this->cli->then_ShouldBe('executed', true);
    }

    function testWithArguments() {
        $this->cli->givenTheCommand_WithTheBody('CommandWithArguments', '
            /**
             * @param int $one
             * @param bool $two
             * @param \DateTime $three
             */
            function doExecute($one, $two, $three) {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('CommandWithArguments', array('12', 'false', '2001-02-13 11:12'));

        $this->cli->then_ShouldBeExactly('one', 12);
        $this->cli->then_ShouldBeExactly('two', false);
        $this->cli->then_ShouldBe('three', new \DateTime('2001-02-13 11:12'));
    }

    function testOptionalArguments() {
        $this->cli->givenTheCommand_WithTheBody('OptionalArguments', '
            function doExecute($one, $two = false, $three = "tres") {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('OptionalArguments', array('uno', 'dos'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'dos');
        $this->cli->then_ShouldBeExactly('three', 'tres');
    }

    function testRegularOptions() {
        $this->cli->givenTheCommand_WithTheBody('RegularOptions', '
            function doExecute($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('RegularOptions', array('--one=uno', '--two=dos'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'dos');
    }

    function testOptionsMixedWithArguments() {
        $this->cli->givenTheCommand_WithTheBody('OptionsMixedWithArguments', '
            function doExecute($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('OptionsMixedWithArguments', array('--two=dos', 'eins', 'zwei'));

        $this->cli->then_ShouldBeExactly('one', 'eins');
        $this->cli->then_ShouldBeExactly('two', 'dos');
    }

    function testLongArgument() {
        $this->cli->givenTheCommand_WithTheBody('CollectingOption', '
            function doExecute($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('CollectingOption', array('uno', '--', 'a', 'long', 'argument'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'a long argument');
    }

    function testArrayOption() {
        $this->cli->givenTheCommand_WithTheBody('ArrayOption', '
            function doExecute($one) {
                $this->one = $one;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('ArrayOption', array('--one=uno', '--one=eins'));

        $this->cli->then_ShouldBe('one', array('uno', 'eins'));
    }

    function testShortOptions() {
        $this->cli->givenTheCommand_WithTheBody('ShortOptions', '
            /**
             * @param $one [o]
             * @param string $two [w]
             * @param boolean $three [t]
             */
            function doExecute($one, $two, $three) {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('ShortOptions', array('-o=uno', '-tw=dos'));

        $this->cli->then_ShouldBe('one', 'uno');
        $this->cli->then_ShouldBe('two', 'dos');
        $this->cli->then_ShouldBe('three', true);
    }

    function testOptionsWithoutEqualSign() {
        $this->cli->givenTheCommand_WithTheBody('OptionsWithoutEqualSign', '
            /**
             * @param $one [o]
             * @param $two [w]
             * @param $three [t]
             */
            function doExecute($one, $two, $three) {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('OptionsWithoutEqualSign', array('-t', 'tres', '--two', 'dos', 'uno'));

        $this->cli->then_ShouldBe('one', 'uno');
        $this->cli->then_ShouldBe('two', 'dos');
        $this->cli->then_ShouldBe('three', 'tres');
    }

    function testInjectConsole() {
        $this->cli->givenTheCommand_WithTheBody('InjectConsole', '
            /**
             * @param $console <-
             */
            function doExecute($one, \watoki\cli\Console $console) {
                $this->executed = $one;
                $console->out->writeLine("Hello World");
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('InjectConsole', array('yes'));

        $this->cli->then_ShouldBe('executed', "yes");
        $this->cli->thenTheOutputShouldBe('Hello World');
    }

    function testInvalidOption() {
        $this->cli->givenTheCommand_WithTheBody('InvalidOption', '
            function doExecute($one) {}
        ');

        $this->cli->whenTryToIRunTheCommand_WithTheArguments('InvalidOption', array('--one', 'uno', '--two', 'dos'));
        $this->cli->thenThereShouldBeAnErrorContaining('Invalid option: two');
    }

    function testInvalidArgument() {
        $this->cli->givenTheCommand_WithTheBody('InvalidArgument', '
            function doExecute($one) {}
        ');

        $this->cli->whenTryToIRunTheCommand_WithTheArguments('InvalidArgument', array('--one', 'uno', 'arg1', 'arg2'));
        $this->cli->thenThereShouldBeAnErrorContaining('Too many arguments: maximum 1, given 2');
    }

}
 