<?php

namespace ExerciseBook\Flysystem\ImageX;

use ExerciseBook\Flysystem\ImageX\Exception\NotImplementedException;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;

class ImageXAdapter implements FilesystemAdapter
{

    public function fileExists(string $path): bool
    {
        // TODO: Implement fileExists() method.
        throw new NotImplementedException();
    }

    public function write(string $path, string $contents, Config $config): void
    {
        // TODO: Implement write() method.
        throw new NotImplementedException();
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        // TODO: Implement writeStream() method.
        throw new NotImplementedException();
    }

    public function read(string $path): string
    {
        // TODO: Implement read() method.
        throw new NotImplementedException();
    }

    public function readStream(string $path)
    {
        // TODO: Implement readStream() method.
        throw new NotImplementedException();
    }

    public function delete(string $path): void
    {
        // TODO: Implement delete() method.
        throw new NotImplementedException();
    }

    public function deleteDirectory(string $path): void
    {
        // TODO: Implement deleteDirectory() method.
        throw new NotImplementedException();
    }

    public function createDirectory(string $path, Config $config): void
    {
        // TODO: Implement createDirectory() method.
        throw new NotImplementedException();
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // TODO: Implement setVisibility() method.
        throw new NotImplementedException();
    }

    public function visibility(string $path): FileAttributes
    {
        // TODO: Implement visibility() method.
        throw new NotImplementedException();
    }

    public function mimeType(string $path): FileAttributes
    {
        // TODO: Implement mimeType() method.
        throw new NotImplementedException();
    }

    public function lastModified(string $path): FileAttributes
    {
        // TODO: Implement lastModified() method.
        throw new NotImplementedException();
    }

    public function fileSize(string $path): FileAttributes
    {
        // TODO: Implement fileSize() method.
        throw new NotImplementedException();
    }

    public function listContents(string $path, bool $deep): iterable
    {
        // TODO: Implement listContents() method.
        throw new NotImplementedException();
    }

    public function move(string $source, string $destination, Config $config): void
    {
        // TODO: Implement move() method.
        throw new NotImplementedException();
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        // TODO: Implement copy() method.
        throw new NotImplementedException();
    }
}
