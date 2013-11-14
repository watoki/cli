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
        $this->cli->thenThereShouldBeAnErrorContaining("Command [nonExisting] not found. Command 'help' lists all commands.");
    }

    function testWithoutArgs() {
        $this->cli->givenTheApplication_WithTheBody('CommandWithoutArgs', '
            function doThis() {
                $this->executed = true;
            }
        ');
        $this->cli->whenIRunTheCommand('this');

        $this->cli->then_ShouldBe('executed', true);
    }

    function testWithArguments() {
        $this->cli->givenTheApplication_WithTheBody('CommandWithArguments', '
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
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('12', 'false', '2001-02-13 11:12'));

        $this->cli->then_ShouldBeExactly('one', 12);
        $this->cli->then_ShouldBeExactly('two', false);
        $this->cli->then_ShouldBe('three', new \DateTime('2001-02-13 11:12'));
    }

    function testOptionalArguments() {
        $this->cli->givenTheApplication_WithTheBody('OptionalArguments', '
            function doThat($one, $two = false, $three = "tres") {
                $this->one = $one;
                $this->two = $two;
                $this->three = $three;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('uno', 'dos'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'dos');
        $this->cli->then_ShouldBeExactly('three', 'tres');
    }

    function testRegularOptions() {
        $this->cli->givenTheApplication_WithTheBody('RegularOptions', '
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('--one=uno', '--two=dos'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'dos');
    }

    function testOptionsMixedWithArguments() {
        $this->cli->givenTheApplication_WithTheBody('OptionsMixedWithArguments', '
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('--two=dos', 'eins', 'zwei'));

        $this->cli->then_ShouldBeExactly('one', 'eins');
        $this->cli->then_ShouldBeExactly('two', 'dos');
    }

    function testLongArgument() {
        $this->cli->givenTheApplication_WithTheBody('CollectingOption', '
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('uno', '--', 'a', 'long', 'argument'));

        $this->cli->then_ShouldBeExactly('one', 'uno');
        $this->cli->then_ShouldBeExactly('two', 'a long argument');
    }

    function testArrayOption() {
        $this->cli->givenTheApplication_WithTheBody('ArrayOption', '
            function doThat($one) {
                $this->one = $one;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('--one=uno', '--one=eins'));

        $this->cli->then_ShouldBe('one', array('uno', 'eins'));
    }

    function testShortOptions() {
        $this->markTestIncomplete();
        $this->cli->givenTheApplication_WithTheBody('ShortOptions', '
            /**
             * @param $one [o]
             * @param string $two [w]
             */
            function doThat($one, $two) {
                $this->one = $one;
                $this->two = $two;
            }
        ');
        $this->cli->whenIRunTheCommand_WithTheArguments('that', array('-o=uno', '-w=dos'));

        $this->cli->then_ShouldBe('one', 'uno');
        $this->cli->then_ShouldBe('two', 'dos');
    }

}
 