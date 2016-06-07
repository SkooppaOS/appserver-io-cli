<?php

namespace AppserverIo\Cli\ClassTraits;

/**
 * Appserver\CLI
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Scott Molinari <scott.molinari@adduco.de>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/description
 * @link      http://www.appserver.io
 */

use Symfony\Component\Process\Process;

/**
 *  Trait to hold data and functions definitions for setting up project repo both for Git and for Composer
 *
 * Author: s.molinari <scott.molinari@adduco.de>
 * Date: 03.06.2016
 */

trait ProjectTrait
{

    /**
     * @var string
     */
    protected $projectName = '';

    /**
     * @var string
     */
    protected $projectDir = '';

    /**
     * @var string
     */
    protected $projectDirName = '';

    /**
     * @var string
     */
    protected $organizationName = '';


    /**
     * @var string
     */
    protected $developersName = '';

    /**
     * @var string
     */
    protected $appDescription = '';

    /**
     * @var string
     */
    protected $composerName = '';

    /**
     * @var string
     */
    protected $composerNamespace = '';

    /**
     * @var string
     */
    protected $composerJson = '';

    /**
     * @var string
     */
    protected $composerJsonSkeletonDir = '/opt/appserver/vendor/skooppaos/appserver-io-cli/src/AppserverIo/Cli/Skeletons';


    /**
     * Checks if the CLI has enough permissions to create the project.
     */
    protected function checkPermissions()
    {
        $projectParentDirectory = dirname($this->projectDir);

        if (!is_writable($projectParentDirectory)) {
            throw new \Exception(sprintf('The CLI does not have enough permissions to write to the "%s" directory.', $this->projectDirName));
        }

        return $this;
    }


    protected function createProjectDir()
    {
        //create project directory
        $mkdir = new Process(sprintf('mkdir -p %s', $this->projectDir));
        $mkdir->run();

        if($mkdir->isSuccessful()) {

            $this->output->writeln("<info>Project directory ".DIRECTORY_SEPARATOR.$this->projectDirName ." was created.</info>\n");
        }

        return $this;
    }

    
    protected function initGitRepo()
    {
        $gitInit = new Process(sprintf('cd %s && git init', $this->projectDir));
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


    protected function setComposerName()
    {
        $this->composerName = strtolower($this->organizationName).'/'.strtolower($this->projectName);

        return $this;

    }


    protected function setComposerNamespace()
    {
        $this->composerNamespace = ucfirst($this->organizationName).'\\\\'.ucfirst($this->projectName);

        return $this;

    }



    protected function getComposerJsonSkeleton()
    {
        $this->composerJson = file_get_contents($this->composerJsonSkeletonDir.DIRECTORY_SEPARATOR.'composer.json');

        return $this;

    }


    protected function modifyComposerJson()
    {
        $replacements = [
        '${composer.name}' => $this->composerName,
        '${composer.description}' => $this->appDescription,
        '${composer.namespace}' => $this->composerNamespace
        ];

        $this->composerJson = strtr($this->composerJson, $replacements);

        return $this;

    }


    protected function saveComposerJson()
    {
        $targetFile = $this->projectDir.DIRECTORY_SEPARATOR.'composer.json';

        file_put_contents($targetFile, $this->composerJson );

        $this->output->writeln("<info>The composer.json file has been successfully created.</info>\n");

        return $this;

    }
    

    protected function installDirectorySkeleton()
    {
        $processCommands = 'mkdir -p %1$s'.DIRECTORY_SEPARATOR.'WEB-INF'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.
                           '%2$s'.DIRECTORY_SEPARATOR.'%3$s &&'.
                           'mkdir -p %1$s'.DIRECTORY_SEPARATOR.'META-INF'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.
                           '%2$s'.DIRECTORY_SEPARATOR.'%3$s &&'.
                           'mkdir -p %1$s'.DIRECTORY_SEPARATOR.'WEB-INF'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.
                           '%2$s'.DIRECTORY_SEPARATOR.'%3$s &&'.
                           'mkdir -p %1$s'.DIRECTORY_SEPARATOR.'META-INF'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.
                           '%2$s'.DIRECTORY_SEPARATOR.'%3$s &&'.
                           'mkdir -p %1$s'.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.
                           '%2$s'.DIRECTORY_SEPARATOR.'%3$s &&'.
                           'mkdir -p %1$s'.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.
                           '%2$s'.DIRECTORY_SEPARATOR.'%3$s';


        $installSkeleton = new Process(sprintf($processCommands,
                                                $this->projectDir,
                                                ucfirst($this->organizationName),
                                                ucfirst($this->projectName)
        ));

        $installSkeleton->run();

        if ($installSkeleton->isSuccessful()) {

            $this->output->writeln("<info>The project directories have been successfully created.</info>\n");

        }else{

            $errorMessage = 'Something went wrong while creating the project directories. Check any errors./n'.
                            'As a last resort, remove the project and start over./n'.
                            'Error: %s';

            throw new \Exception(sprintf($errorMessage, $installSkeleton->getErrorOutput()));
        }

        return $this;

    }
    



    protected function installStaticFiles()
    {

    }

    protected function installDynamicFiles()
    {

    }

}