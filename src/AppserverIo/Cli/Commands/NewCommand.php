<?php
namespace AppserverIo\Cli\Commands;

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

use AppserverIo\Cli\ClassTraits\EmailTrait;
use AppserverIo\Cli\ClassTraits\ProjectTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Console\Helper\QuestionHelper;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;
use AppserverIo\Cli\Exception\AbortException;
use Templates;


/**
 * This command creates new projects.
 *
 * @author Scott Molinari <scott.molinari@adduco.de>
 * Date: 25.05.2016
 */
class NewCommand extends Command
{

    use ProjectTrait;

    const ROOT_DIR = '/opt/appserver/webapps';

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var string
     */
    protected $option = '';

    /**
     * @var string
     */
    protected $packages = '';

    /**
     * @var GitRepo
     */
    protected $gitRepo;


    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Creates a new appserver project.')
            ->addArgument('projectName', InputArgument::OPTIONAL, 'Project name of the new project.')
            ->addOption('with', 'w', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->setHelp(<<<EOT
The <info>new</info> command creates a basic application for appserver in the "/opt/appserver/webapps" directory.

You can add the name of your new project as an argument. Example: <info>appserver new blog</info>

You can also add the "--with" or "-w" option, to also load specially built appserver extensions or applications. 

Currently the only extensions supported are "example", which loads a full example application or "routlt", which loads the routlt extension.

EOT
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->input = $input;
        $this->output = $output;
        $adapter = new Adapter(self::ROOT_DIR);
        $this->fs = new Filesystem($adapter);

        $this->projectName = trim($input->getArgument('projectName'));
        $this->packages = $input->getOption('with');
        //$this->setOption($this->option);
        //$this->setPackageOptions($this->packages);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this
                ->getProjectName()
                ->getDirectoryName()
                ->getOrganizationName()
                ->getDevelopersName()
                ->getAppDescription()
                ->checkPermissions()
                ->createProject();

                //->cleanUp()
                //->displayInitializationResult();
        } catch (AbortException $e) {
            aborted:

            $output->writeln('');
            $output->writeln('<error>Aborting initialization and cleaning up.</error>');

            $this->cleanUp();

            return 1;
        } catch (\Exception $e) {

            $this->cleanUp();
            throw $e;
        }
    }


    protected function getProjectName()
    {
        if ($this->projectName === '') {

            $question = new Question (
                'Please enter the name of the project (default: my-app): ', 'my-app');

            $helper = $this->getHelper('question');

            $this->projectName = $helper->ask($this->input, $this->output, $question);
        }

        return $this;
    }


    protected function getDirectoryName()
    {
        $defaultDirectoryName = strtolower($this->projectName);

        $question = new Question (
            'Please enter the name of the project directory (default: '.$defaultDirectoryName.'): ',
            $this->projectName);

        $helper = $this->getHelper('question');

        $question->setValidator(function ($value) {

            $errorMessage = "There is already a '%s' directory created. Please choose a different directory name.";

            if ($this->fs->has(DIRECTORY_SEPARATOR.$value)) {
                throw new \Exception(sprintf($errorMessage, $value
                ));
            }

            return $value;
        });

        $question->setMaxAttempts(3);

        $this->projectDirName = $helper->ask($this->input, $this->output, $question);

        $this->projectDir = self::ROOT_DIR.DIRECTORY_SEPARATOR.$this->projectDirName;

        return $this;
    }
    

    protected function getOrganizationName()
    {

        $question = new Question (
            'Please enter your organization\'s name (default: my-org): ',
            'my-org');

        $helper = $this->getHelper('question');

        $this->organizationName = $helper->ask($this->input, $this->output, $question);

        return $this;
    }
    

    protected function getDevelopersName()
    {

        $question = new Question (
            'Please enter your name (example: John Dev <john.dev@my-org.tmp>): ');

        $helper = $this->getHelper('question');


        $question->setValidator(function ($value) {

            if (preg_match('/^(?P<name>[- \.,\p{L}\p{N}\'’]+) <(?P<email>.+?)>$/u', $value, $match)) {
                if ($this->isValidEmail($match['email'])) {
                    return $value;
                }
            }

            $errorMessage = 'Invalid developer name string. It must be in the format: '.
                            'John Dev <john.dev@my-org.tmp>';

            throw new \InvalidArgumentException($errorMessage);

        });

        $question->setMaxAttempts(5);

        $this->developersName = $helper->ask($this->input, $this->output, $question);

        return $this;
    }

    
    protected function getAppDescription()
    {
        $question = new Question (
            'Please enter a description of your application (default: A blank description): ',
            'A blank Description');

        $helper = $this->getHelper('question');

        $this->appDescription = $helper->ask($this->input, $this->output, $question);

        return $this;
    }


    protected function check()
    {
        $this->output->writeln("\n Skeleton Dir: ".$this->skeletonsDir);

        return $this;
    }

    
    protected function createProject()
    {
        $this->output->writeln("\n<info>Preparing project...</info>\n");
        if($this->packages){

        } else {

            $this
                ->createProjectDir()
                ->initGitRepo()
                ->setComposerName()
                ->setComposerNamespace()
                ->getComposerJsonSkeleton()
                ->modifyComposerJson()
                ->saveComposerJson()
                ->installDirectorySkeleton();

        }

            //->installStaticFiles()
            //->installDynamicFiles();
        //if with example
           //copy composer.json file with template for example
       // if (file_put_contents($this->installDir . "/composer.json", $skeleton)) {
       //     $this->output->writeln('<info>composer.json has been generated</info>');
        //run composer install
        //move /src files to project directory
        // cp -n -R /opt/appserver/webapps/test/vendor/appserver-io-apps/example/src/* /opt/appserver/webapps/test

        //remove /appserver-io-apps directory
    }

    
    protected function cleanup()
    {

    }

    
    protected function displayInitializationResult()
    {
        $this->output->writeln(" Command Name: ". $this->getName()."\n");
        $this->output->writeln(" Project Name: ". $this->projectName."\n");
        $this->output->writeln(" with option value(s): ". implode('|', $this->packages)."\n");
    }

    
    protected function install()
    {
        $install = new Process(sprintf('cd %s && php composer.phar install',  $this->projectDir));
        $install->run();

        if ($install->isSuccessful()) {
            $this->output->writeln('<info>Packages successfully installed</info>');

            return true;
        }

        $this->failingProcess = $install;
        return false;
    }
    

    protected function isValidEmail($email)
    {
        // assume it's valid if we can't validate it
        if (!function_exists('filter_var')) {
            return true;
        }

        // php <5.3.3 has a very broken email validator, so bypass checks
        if (PHP_VERSION_ID < 50303) {
            return true;
        }

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}