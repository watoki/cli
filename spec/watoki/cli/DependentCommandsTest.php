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
        $this->cli->givenTheCommand('TreeFour');

        $this->cli->givenTheCommand_DependsOn('TreeOne', 'TreeTwo');
        $this->cli->givenTheCommand_DependsOn('TreeOne', 'TreeFour');

        $this->cli->givenTheCommand_DependsOn('TreeTwo', 'TreeThree');
        $this->cli->givenTheCommand_DependsOn('TreeTwo', 'TreeFour');

        $this->cli->givenTheCommand_DependsOn('TreeThree', 'TreeFour');

        $this->cli->whenIRunTheCommand('TreeOne');

        $this->cli->thenTheOutputShouldBe(
            "X:TreeFour" . PHP_EOL .
            "X:TreeThree" . PHP_EOL .
            "X:TreeTwo" . PHP_EOL .
            "X:TreeOne");
    }
}
 