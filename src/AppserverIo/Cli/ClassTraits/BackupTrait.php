<?php

namespace AppserverIo\Cli\ClassTraits;

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
