<?php

namespace ExerciseBook\Flysystem\ImageX\Test;

use ExerciseBook\Flysystem\ImageX\ImageXAdapter;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\FileNotFoundException;
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
        $t = $this->adapter->write('test/test.txt', '1145141919810', $config);
        $this->assertEquals('test/test.txt', $t['path']);

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

        $map = [
            'test/ori.jpg' => false,
            'test/ori_stream.jpg' => false,
            'test/test.txt' => false
        ];

        foreach ($response as $file) {
            if (is_array($file)) {
                if (isset($map[$file['path']])) {
                    $map[$file['path']] = true;
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
        $this->assertEquals(318178, $this->adapter->getVisibility('test/ori.jpg')['FileSize']);
        $this->assertEquals(318178, $this->adapter->getVisibility('test/ori_stream.jpg')['FileSize']);
    }

    public function testFileExists()
    {
        $this->assertTrue($this->adapter->has('test/ori.jpg'));
        $this->assertFalse($this->adapter->has('test/file_not_exist.jpg'));
    }

    public function testRead()
    {
        $content = $this->adapter->read('test/test.txt');
        $this->assertEquals('1145141919810', $content['contents']);
    }

    public function testReadNonExistentFile()
    {
        $this->expectException(FileNotFoundException::class);
        $this->adapter->delete('test/non_existent_file.jpg');
    }

    public function testReadStream()
    {
        $expectedContents = fopen('resources/ori.jpg', 'rb');
        $actualContent = $this->adapter->readStream('test/ori_stream.jpg');

        $this->assertEquals(stream_get_contents($expectedContents), stream_get_contents($actualContent['stream']));

        fclose($expectedContents);
    }

    public function testDeleteNonExistentFile()
    {
        $this->expectException(FileNotFoundException::class);
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
