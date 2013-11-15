<?php
namespace spec\watoki\cli;

use spec\watoki\cli\fixtures\CliApplicationFixture;
use watoki\scrut\Specification;

/**
 * @property CliApplicationFixture cli <-
 */
class RunCommandTest extends Specification {

    function testNonExistingCommand() {
        $this->cli->whenTryToIRunTheSubCommand('nonExisting');
        $this->cli->thenThereShouldBeAnErrorContaining("Command [nonExisting] not found. Command 'help' lists all commands.");
    }

    function testWithoutArgs() {
        $this->cli->givenTheMultiCommand_WithTheBody('CommandWithoutArgs', '
            function doThis() {
                $this->executed = true;
            }
        ');
        $this->cli->whenIRunTheSubCommand('this');

        $this->cli->then_ShouldBe('executed', true);
    }

    function testWithArguments() {
        $this->cli->givenTheMultiCommand_WithTheBody('CommandWithArguments', '
            /**
             * @param int $one
             * @param bool $two
             * @param \DateTime $three
             */
            function doThat($one, $two, $three) {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('12', 'false', '2001-02-13 11:12'));

        $this->cli->then_ShouldBeExactly('one', 12);
        $this->cli->then_ShouldBeExactly('two', false);
        $this->cli->then_ShouldBe('three', new \DateTime('2001-02-13 11:12'));
    }

    function testOptionalArguments() {
        $this->cli->givenTheMultiCommand_WithTheBody('OptionalArguments', '
            function doThat($one, $two = false, $three = "tres") {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('uno', 'dos'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'dos');
        $this->cli->then_ShouldBeExactly('three', 'tres');
    }

    function testRegularOptions() {
        $this->cli->givenTheMultiCommand_WithTheBody('RegularOptions', '
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('--one=uno', '--two=dos'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'dos');
    }

    function testOptionsMixedWithArguments() {
        $this->cli->givenTheMultiCommand_WithTheBody('OptionsMixedWithArguments', '
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('--two=dos', 'eins', 'zwei'));

        $this->cli->then_ShouldBeExactly('one', 'eins');
        $this->cli->then_ShouldBeExactly('two', 'dos');
    }

    function testLongArgument() {
        $this->cli->givenTheMultiCommand_WithTheBody('CollectingOption', '
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('uno', '--', 'a', 'long', 'argument'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'a long argument');
    }

    function testArrayOption() {
        $this->cli->givenTheMultiCommand_WithTheBody('ArrayOption', '
            function doThat($one) {
                $this->one = $one;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('--one=uno', '--one=eins'));

        $this->cli->then_ShouldBe('one', array('uno', 'eins'));
    }

    function testShortOptions() {
        $this->cli->givenTheMultiCommand_WithTheBody('ShortOptions', '
            /**
             * @param $one [o]
             * @param string $two [w]
             * @param boolean $three [t]
             */
            function doThat($one, $two, $three) {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('-o=uno', '-tw=dos'));

        $this->cli->then_ShouldBe('one', 'uno');
        $this->cli->then_ShouldBe('two', 'dos');
        $this->cli->then_ShouldBe('three', true);
    }

    function testOptionsWithoutEqualSign() {
        $this->cli->givenTheMultiCommand_WithTheBody('OptionsWithoutEqualSign', '
            /**
             * @param $one [o]
             * @param $two [w]
             * @param $three [t]
             */
            function doThat($one, $two, $three) {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheSubCommand_WithTheArguments('that', array('-t', 'tres', '--two', 'dos', 'uno'));

        $this->cli->then_ShouldBe('one', 'uno');
        $this->cli->then_ShouldBe('two', 'dos');
        $this->cli->then_ShouldBe('three', 'tres');
    }

}
 