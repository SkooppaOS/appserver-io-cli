<?php
namespace AppserverIo\Cli\Commands;

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
     * @var string
     */
    protected $projectDir = '';

    /**
     * @var string
     */
    protected $projectDirName = '';

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
            ->addArgument('projectName', InputArgument::OPTIONAL, 'Project name and directory where the new project will be created.')
            ->addOption('with', 'w', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
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
            // Guzzle can wrap the AbortException in a GuzzleException
            if ($e->getPrevious() instanceof AbortException) {
                goto aborted;
            }

            $this->cleanUp();
            throw $e;
        }
    }


    protected function getProjectName()
    {
        if ($this->projectName === '') {
            $defaultProjectName = 'MyApp';
            $question = 'Please enter the name of the project (default: '.$defaultProjectName.'): ';
            $this->projectName = $this->askQuestion($question, $defaultProjectName);
        }

        return $this;
    }


    protected function getDirectoryName()
    {
       $question = new Question (
            'Please enter the name of the project directory (default: '.$this->projectName.'): ',
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


    protected function getAppDescription()
    {
        $question = new Question (
            'Please enter a description of your application (default: A blank description): ',
            'A blank Description');

        $helper = $this->getHelper('question');

        $this->appDescription = $helper->ask($this->input, $this->output, $question);

        return $this;
    }

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

    protected function checkWith ()
    {
        $this->output->writeln("\n This is with ". join(',',$this->packages)." packages.");
        $this->output->writeln("\n Org Name: ".$this->organizationName);
    }


    protected function createProject()
    {
        $this->output->writeln("\n<info>Preparing project...</info>\n");

        $this
            ->createProjectDir()
            ->initGitRepo();
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

    private function askQuestion ($question, $default = null, $attempts=0)
    {
        $question = new Question ($question, $default);

        $question->setMaxAttempts($attempts);

        $helper = $this->getHelper('question');

        return $helper->ask($this->input, $this->output, $question);
    }

}