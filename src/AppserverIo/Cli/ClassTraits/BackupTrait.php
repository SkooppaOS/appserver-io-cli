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
    
/**
 * BackupTrait
 *
 * @author Martin Mohr <mohrwurm@gmail.com>
 * @since 24.04.16
 */
trait BackupTrait
{
    /**
     * do backup from file
     *
     * @param $fileName
     *
     * @return bool
     */
    public function doBackup($fileName)
    {
        if (copy($fileName, $fileName . '.' . time() . '.bak')) {
            return true;
        }

        return false;
    }
}
