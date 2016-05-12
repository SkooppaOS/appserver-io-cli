<?php
namespace AppserverIo\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Exception\IOException;
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

    const ROOT_DIR = '/opt/appserver/webapps';

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $projectDir;

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


    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Creates a new appserver project.')
            ->addArgument('projectName', InputArgument::REQUIRED, 'Project name and directory where the new project will be created.')
            ->addOption('with', 'w', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
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
        $this->projectDir = self::ROOT_DIR.DIRECTORY_SEPARATOR.$this->projectName;
        //$this->setOption($this->option);
        //$this->setPackageOptions($this->packages);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this
                ->checkProjectName()
                ->checkPermissions()
                ->initializeProject();
                //->askWebSiteQuestions()
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


    protected function checkProjectName()
    {
        if ($this->fs->has(DIRECTORY_SEPARATOR.$this->projectName)) {
            throw new \RuntimeException(sprintf(
                "There is already a '%s' project created.\n".
                'Change your project name to create a new project please.',
                $this->projectName
            ));
        }

        return $this;
    }

    /**
     * Checks if the initializer has enough permissions to create the project.
     */
    protected function checkPermissions()
    {
        $projectParentDirectory = dirname($this->projectDir);

        if (!is_writable($projectParentDirectory)) {
            throw new IOException(sprintf('Initializer does not have enough permissions to write to the "%s" directory.', $projectParentDirectory));
        }

        return $this;
    }

    protected function cleanup()
    {

    }

    protected function initializeProject()
    {
        $this->output->writeln("\n Preparing project...\n");

        //create project directory
        mkdir("$this->projectDir", 0755);

        $this->output->writeln("\n Project directory ". DIRECTORY_SEPARATOR.$this->projectName ." was created.\n");
        //if with example
           //copy composer.json file with template for example
       // if (file_put_contents($this->installDir . "/composer.json", $skeleton)) {
       //     $this->output->writeln('<info>composer.json has been generated</info>');
        //run composer install
        //move /src files to project directory
        // cp -n -R /opt/appserver/webapps/test/vendor/appserver-io-apps/example/src/* /opt/appserver/webapps/test

        //remove /appserver-io-apps directory
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
            $output->writeln('<info>Packages succesfully installed</info>');

            return true;
        }

        $this->failingProcess = $install;
        return false;
    }

}