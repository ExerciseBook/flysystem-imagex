<?php

namespace ExerciseBook\Flysystem\ImageX;

use ExerciseBook\Flysystem\ImageX\Exception\NotImplementedException;
use PHPUnit\Framework\TestCase;

class ImageXAdapterTest extends TestCase
{
    /**
     * @var ImagexAdapter
     */
    private $adapter;

    public function testWriteStream()
    {
        $this->expectException(NotImplementedException::class);
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
        $this->expectException(NotImplementedException::class);

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

    protected function setUp(): void
    {
        $this->adapter = new ImagexAdapter();
    }
}
