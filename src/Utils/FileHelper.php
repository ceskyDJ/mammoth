<?php

/**
 * This is the part of the Mammoth framework (https://github.com/ceskyDJ/mammoth)
 */

declare(strict_types = 1);

namespace Mammoth\Utils;

use Mammoth\Exceptions\NonExistingFileException;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function unlink;

/**
 * Helper for working with files
 *
 * @author Michal Šmahel (ceskyDJ) <admin@ceskydj.cz>
 * @package Mammoth\Utils
 */
final class FileHelper
{
    /**
     * Create empty file
     *
     * @param string $file File address
     */
    public function createFile(string $file): void
    {
        file_put_contents($file, "");
    }

    /**
     * Add a record to file
     *
     * @param string $file File for editing
     * @param $content string Content for adding
     * @param $separator string Delimiter (Default: line break)
     *
     * @throws \Mammoth\Exceptions\NonExistingFileException Invalid file
     */
    public function addToFile(string $file, string $content, string $separator = "\n"): void
    {
        $oldContent = $this->getFileContent($file);

        $this->updateFile($file, $oldContent.$separator.$content);
    }

    /**
     * Returns file content
     *
     * @param string $file File for editing
     *
     * @return string File content
     * @throws \Mammoth\Exceptions\NonExistingFileException Invalid file
     */
    public function getFileContent(string $file): string
    {
        if (!is_file($file)) {
            throw new NonExistingFileException("Invalid file address has been entered ({$file})");
        }

        return file_get_contents($file);
    }

    /**
     * Updates file content
     *
     * @param string $file File for editing
     * @param $newContent string New content
     *
     * @throws \Mammoth\Exceptions\NonExistingFileException Invalid file
     */
    public function updateFile(string $file, string $newContent): void
    {
        if (!is_file($file)) {
            throw new NonExistingFileException("Invalid file address has been entered ({$file})");
        }

        file_put_contents($file, $newContent);
    }

    /**
     * Remove file content (clean the file)
     *
     * @param string $file File for editing
     *
     * @throws \Mammoth\Exceptions\NonExistingFileException Invalid file
     */
    public function cleanFile(string $file): void
    {
        $this->updateFile($file, "");
    }

    /**
     * Remove the file
     *
     * @param string $file File for editing
     *
     * @throws \Mammoth\Exceptions\NonExistingFileException Invalid file
     */
    public function deleteFile(string $file): void
    {
        if (!is_file($file)) {
            throw new NonExistingFileException("Invalid file address has been entered ({$file})");
        }

        unlink($file);
    }

    /**
     * Returns parsed file
     *
     * @param string $file File for editing
     *
     * @return string[] Parsed file (by lines)
     * @throws \Mammoth\Exceptions\NonExistingFileException Invalid file
     */
    public function parseFile(string $file): array
    {
        return explode("\n", $this->getFileContent($file));
    }
}