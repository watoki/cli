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
}
 