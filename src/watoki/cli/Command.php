<?php
namespace watoki\cli;
 
interface Command {

    /**
     * @param CliApplication $application The application that executes the Command
     * @param array $arguments Arguments as produced by the Parser
     * @return void
     */
    public function execute(CliApplication $application, array $arguments);

}
 