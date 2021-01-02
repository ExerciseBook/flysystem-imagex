<?php

namespace ExerciseBook\Flysystem\ImageX\Test;

use ExerciseBook\Flysystem\ImageX\Exception\NotImplementedException;
use ExerciseBook\Flysystem\ImageX\ImageXAdapter;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\UnableToDeleteFile;
use PHPUnit\Framework\TestCase;

require('Config.php');

class ImageXAdapterTest extends TestCase
{
    /**
     * @var ImagexAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->adapter = new ImagexAdapter(new Config());
    }

    public function testWrite()
    {
        $config = new FlysystemConfig();
        $this->adapter->write('test/test.txt', '1145141919810', $config);

        $contents = file_get_contents('resources/ori.jpg');
        $this->adapter->write('test/ori.jpg', $contents, $config);
        $this->assertTrue(true);
    }

    public function testWriteStream()
    {
        $config = new FlysystemConfig();
        $contents = fopen('resources/ori.jpg', 'rb');
        $this->adapter->writeStream('test/ori_stream.jpg', $contents, $config);
        fclose($contents);
        $this->assertTrue(true);
    }

    public function testDeleteDirectory()
    {
        // There is no directory operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testReadStream()
    {
        $expectedContents = fopen('resources/ori.jpg', 'rb');
        $actualContent = $this->adapter->readStream('test/ori_stream.jpg');

        $this->assertEquals(stream_get_contents($expectedContents), stream_get_contents($actualContent));

        fclose($expectedContents);
    }

    public function testRead()
    {
        $content = $this->adapter->read('test/test.txt');
        $this->assertEquals('1145141919810', $content);
    }

    public function testListContents()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testCreateDirectory()
    {
        // There is no directory operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testMimeType()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testDeleteNonExistentFile()
    {
        $this->expectException(UnableToDeleteFile::class);
        $this->adapter->delete('test/non_existent_file.jpg');
        $this->assertTrue(true);
    }

    public function testDelete()
    {
        $this->adapter->delete('test/test.txt');
        $this->adapter->delete('test/ori.jpg');
        $this->adapter->delete('test/ori_stream.jpg');
        $this->assertTrue(true);
    }

    public function testSetVisibility()
    {
        // There is no visibility operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testLastModified()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testVisibility()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testFileExists()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testCopy()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testFileSize()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }

    public function testMove()
    {
        // TODO
        $this->expectException(NotImplementedException::class);
        throw(new NotImplementedException());
    }
}
