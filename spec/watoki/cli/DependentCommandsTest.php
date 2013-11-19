<?php
namespace spec\watoki\cli;
use spec\watoki\cli\fixtures\CliApplicationFixture;
use watoki\scrut\Specification;

/**
 * @property CliApplicationFixture cli <-
 */
class DependentCommandsTest extends Specification {

    protected function background() {
        $this->cli->givenADependentCommandGroup();
    }

    function testOneDependentCommand() {
        $this->cli->givenTheCommand('OneDepending');
        $this->cli->givenTheCommand('OneDependency');

        $this->cli->givenTheCommand_DependsOn('OneDepending', 'OneDependency');

        $this->cli->whenIRunTheCommand('OneDepending');

        $this->cli->thenTheOutputShouldBe(
            "X:OneDependency" . PHP_EOL .
            "X:OneDepending");
    }

    function testDoNotRepeat() {
        $this->cli->givenTheCommand('TreeOne');
        $this->cli->givenTheCommand('TreeTwo');
        $this->cli->givenTheCommand('TreeThree');
        $this->cli->givenTheCommand_WithTheBody('TreeFour', '
            function doExecute($arg, \watoki\cli\Console $c) {
                $c->out->writeLine("X:TreeFour($arg)");
            }');

        $this->cli->givenTheCommand_DependsOn('TreeOne', 'TreeTwo');
        $this->cli->givenTheCommand_DependsOn('TreeOne', 'TreeThree');
        $this->cli->givenTheCommand_DependsOn_WithTheArguments('TreeOne', 'TreeFour', array('one'));

        $this->cli->givenTheCommand_DependsOn('TreeTwo', 'TreeThree');
        $this->cli->givenTheCommand_DependsOn_WithTheArguments('TreeTwo', 'TreeFour', array('two'));

        $this->cli->givenTheCommand_DependsOn_WithTheArguments('TreeThree', 'TreeFour', array('two'));

        $this->cli->whenIRunTheCommand('TreeOne');

        $this->cli->thenTheOutputShouldBe(
            "X:TreeFour(two)" . PHP_EOL .
            "X:TreeThree" . PHP_EOL .
            "X:TreeTwo" . PHP_EOL .
            "X:TreeFour(one)" . PHP_EOL .
            "X:TreeOne");
    }

    function testCircles() {
        $this->cli->givenTheCommand('CircleOne');
        $this->cli->givenTheCommand('CircleTwo');
        $this->cli->givenTheCommand('CircleThree');

        $this->cli->givenTheCommand_DependsOn('CircleOne', 'CircleTwo');
        $this->cli->givenTheCommand_DependsOn('CircleTwo', 'CircleThree');
        $this->cli->givenTheCommand_DependsOn('CircleThree', 'CircleOne');

        $this->cli->whenTryToIRunTheCommand('CircleOne');

        $this->cli->thenThereShouldBeAnErrorContaining('Circular dependency detected: [CircleOne] -> [CircleTwo] -> [CircleThree] -> [CircleOne]');
    }
}
 