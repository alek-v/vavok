<?php

namespace App\Traits;

trait Files {
    /**
     * Clear file
     *
     * @param string $file
     * @return bool
     */
    public function clearFile(string $file): bool
    {
        file_put_contents($file, '');

        return true;
    }

    /**
     * Count number of lines in the file
     *
     * @param string $file
     * @return int
     */
    public function linesInFile(string $file): int
    {
        $count_lines = 0;
        if (file_exists($file)) $count_lines = count(file($file));

        return $count_lines;
    }

    /**
     * Limit number of lines in file
     *
     * @param string $file_name
     * @param integer $max
     * @return void
     */
    public function limitFileLines(string $file_name, int $max = 100): void
    {
        $file = file($file_name);
        $i = count($file);
        if ($i >= $max) {
            $fp = fopen($file_name, "w");
            flock($fp, LOCK_EX);
            unset($file[0]);
            unset($file[1]);
            fputs($fp, implode('', $file));
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Read file from data directory
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
     */
    public function writeDataFile(string $filename, string $data, ?int $append_data = null): void
    {
        if ($append_data == 1) {
            file_put_contents(STORAGEDIR . $filename, $data, FILE_APPEND);
            return;
        }

        file_put_contents(STORAGEDIR . $filename, $data, LOCK_EX);
    }
}