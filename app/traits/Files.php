<?php

namespace App\Traits;

use App\Classes\FileException;

trait Files {
    /**
     * Clear file
     *
     * @param string $filename
     * @return bool
     * @throws FileException
     */
    public function clearFile(string $filename): bool
    {
        $result = file_put_contents($filename, '');

        if ($result === false) throw new FileException('File ' . $filename . ' is not writeable.');

        return true;
    }

    /**
     * Count number of lines in the file
     *
     * @param string $filename
     * @return int
     * @throws FileException
     */
    public function linesInFile(string $filename): int
    {
        $count_lines = 0;
        if (file_exists($filename)) $count_lines = count(file($filename));
        else throw new FileException('File ' . $filename . ' does not exist.');

        return $count_lines;
    }

    /**
     * Limit number of lines in file located in storage directory
     *
     * @param string $filename
     * @param integer $max
     * @return void
     * @throws FileException
     */
    public function limitFileLines(string $filename, int $max = 100): void
    {
        $file = file(STORAGEDIR . $filename);
        $i = count($file);
        if ($i > $max) {
            unset($file[0]);
            $file_content = implode('', $file);
            $this->writeDataFile($filename, $file_content);
        }
    }

    /**
     * Read file from storage directory
     *
     * @param string $filename
     * @return array
     */
    public function getDataFile(string $filename): array
    {
        return file(STORAGEDIR . $filename);
    }

    /**
     * Write to the file in storage directory
     *
     * @param string $filename
     * @param string $data
     * @param ?int $append_data, 1 is to append new data
     * @return void
     * @throws FileException
     */
    public function writeDataFile(string $filename, string $data, ?int $append_data = null): void
    {
        if ($append_data == 1) {
            $result = file_put_contents(STORAGEDIR . $filename, $data, FILE_APPEND);
        } else {
            $result = file_put_contents(STORAGEDIR . $filename, $data, LOCK_EX);
        }

        if (!$result) throw new FileException('File ' . STORAGEDIR . $filename . ' is not writeable.');
    }

    /**
     * Make file in storage directory writeable
     * @param string $filename
     * @return void
     * @throws FileException
     */
    public function makeFileWriteable(string $filename): void
    {
        $result = chmod(STORAGEDIR . $filename, 0777);
        if (!$result) throw new FileException('Cannot change CHMOD permissions of the file ' . STORAGEDIR . $filename);
    }
}