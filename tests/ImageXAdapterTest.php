<?php

namespace ExerciseBook\Flysystem\ImageX\Test;

use ExerciseBook\Flysystem\ImageX\ImageXAdapter;
use ExerciseBook\Flysystem\ImageX\Test\Test\Exception\NotImplementedException;
use League\Flysystem\Config as FlysystemConfig;
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
        $this->adapter = new ImagexAdapter(Config::$imageXRegion, Config::$accessKey, Config::$secretKey,
            Config::$imageXServiceId);
    }

    public function testDeleteDirectory()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testReadStream()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testRead()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testListContents()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testWrite()
    {
        $config = new FlysystemConfig();
        $this->adapter->write('test/test.txt', '1145141919810', $config);

        $contents = file_get_contents('resources/ori.jpg');
        $this->adapter->write('test/ori.jpg', $contents, $config);
        $this->assertTrue(true);
    }

    public function testCreateDirectory()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testMimeType()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testDelete()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testSetVisibility()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testLastModified()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testVisibility()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testFileExists()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testCopy()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testFileSize()
    {
        $this->expectException(NotImplementedException::class);

    }

    public function testMove()
    {
        $this->expectException(NotImplementedException::class);

    }
}
