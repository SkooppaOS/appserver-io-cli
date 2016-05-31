<?php

require('/opt/appserver/vendor/autoload.php');


use AppserverIo\Cli\Application;
use AppserverIo\Cli\Commands\AboutCommand;
use AppserverIo\Cli\Commands\NewCommand;
use AppserverIo\Cli\Commands\ServerConfig;
use AppserverIo\Cli\Commands\ServerCommand;
use AppserverIo\Cli\Commands\ServerParameterCommand;
use AppserverIo\Cli\Commands\ServerRestartCommand;
use AppserverIo\Cli\Commands\ServletCommand;

$appVersion = '0.1.1-DEV';

// Windows uses Path instead of PATH
if (!isset($_SERVER['PATH']) && isset($_SERVER['Path'])) {
    $_SERVER['PATH'] = $_SERVER['Path'];
}

$app = new Application('Appserver CLI', $appVersion);
$app->add(new AboutCommand($appVersion));
$app->add(new NewCommand());
$app->add(new ServerConfig());
$app->add(new ServerCommand());
$app->add(new ServerParameterCommand());
$app->add(new ServerRestartCommand());
$app->add(new ServletCommand());
$app->setDefaultCommand('help');
$app->run();