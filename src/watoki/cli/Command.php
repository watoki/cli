<?php
namespace watoki\cli;
 
interface Command {

    /**
     * @param Console $console
     * @param array $arguments Arguments as produced by the Parser
     * @return void
     */
    public function execute(Console $console, array $arguments);

    /**
     * @return null|string
     */
    public function getDescription();

    /**
     * @return null|string
     */
    public function getHelpText();

}
 