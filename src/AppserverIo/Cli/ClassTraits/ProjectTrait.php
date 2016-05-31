<?php

namespace AppserverIo\Cli\ClassTraits;

/**
 *  Trait to hold data and functions definitions for setting up project repo both for Git and for Composer
 */

use Symfony\Component\Process\Process;


trait ProjectTrait
{

    /**
     * @var string
     */
    protected $projectName = '';

    /**
     * @var string
     */
    protected $organizationName = '';

    /**
     * @var string
     */
    protected $appDescription = '';


    protected function createProjectDir()
    {
        //create project directory
        $mkdir = new Process(sprintf('mkdir -p %s', $this->projectDir));
        $mkdir->run();

        if($mkdir->isSuccessful()) {

            $this->output->writeln("<info>Project directory ". DIRECTORY_SEPARATOR.$this->projectDirName ." was created.</info>\n");
        }

        return $this;
    }

    
    protected function initGitRepo()
    {
        $gitInit = new Process(sprintf('cd %s && git init', $this->projectDir));#
        $gitInit->run();

        if ($gitInit->isSuccessful()) {
            $this->output->writeln("<info>Git repo has been initialized.</info>\n");
        }else{

            $errorMessage = "Something went wrong while initializing the Git repo./n";
            $errorMessage .= "Error: %s";

            throw new \Exception(sprintf($errorMessage, $gitInit->getErrorOutput()));
        }

        return $this;

    }

}