<?php

namespace ExerciseBook\Flysystem\ImageX\Test;

use ExerciseBook\Flysystem\ImageX\ImageXAdapter;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\FileAttributes;
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
//        $this->adapter = new ImageXAdapter(new Config());
        $this->adapter = new ImageXAdapter(Config::$arrayConfig);
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

    public function testListContents()
    {
        $response = $this->adapter->listContents('test/', true);
        $prefix = $this->adapter->getUriPrefix();
        $map = [
            $prefix . '/test/ori.jpg' => false,
            $prefix . '/test/ori_stream.jpg' => false,
            $prefix . '/test/test.txt' => false
        ];

        foreach ($response as $file) {
            if ($file instanceof FileAttributes) {
                if (isset($map[$file->path()])) {
                    $map[$file->path()] = true;
                }
            }
        }

        $result = true;
        foreach ($map as $key => $value) {
            $result = $result && $value;
        }
        $this->assertTrue($result);
    }

    public function testVisibility()
    {
//        $this->assertEquals(strlen('1145141919810'), $this->adapter->visibility('test/test.txt')->fileSize());
        $this->assertEquals(318178, $this->adapter->visibility('test/ori.jpg')->fileSize());
        $this->assertEquals(318178, $this->adapter->visibility('test/ori_stream.jpg')->fileSize());
    }

    public function testFileExists()
    {
        $this->assertTrue($this->adapter->fileExists('test/ori.jpg'));
        $this->assertFalse($this->adapter->fileExists('test/file_not_exist.jpg'));
    }

    public function testRead()
    {
        $content = $this->adapter->read('test/test.txt');
        $this->assertEquals('1145141919810', $content);
    }

    public function testReadNonExistentFile()
    {
        $this->expectException(UnableToDeleteFile::class);
        $this->adapter->delete('test/non_existent_file.jpg');
    }

    public function testReadStream()
    {
        $expectedContents = fopen('resources/ori.jpg', 'rb');
        $actualContent = $this->adapter->readStream('test/ori_stream.jpg');

        $this->assertEquals(stream_get_contents($expectedContents), stream_get_contents($actualContent));

        fclose($expectedContents);
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

    public function testMimeType()
    {
        $this->assertTrue(true);
    }

    public function testFileSize()
    {
        $this->assertTrue(true);
    }

    public function testLastModified()
    {
        $this->assertTrue(true);
    }

    public function testSetVisibility()
    {
        // There is no visibility operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testCopy()
    {
        // There is no copy operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testMove()
    {
        // There is no copy operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testCreateDirectory()
    {
        // There is no directory operation for ImageX so far
        $this->assertTrue(true);
    }

    public function testDeleteDirectory()
    {
        // There is no directory operation for ImageX so far
        $this->assertTrue(true);
    }

}
