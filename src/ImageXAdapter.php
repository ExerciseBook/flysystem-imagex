<?php

namespace ExerciseBook\Flysystem\ImageX;

use ExerciseBook\Flysystem\ImageX\Exception\NotImplementedException;
use GuzzleHttp\Client;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Volc\Service\ImageX;

class ImageXAdapter implements FilesystemAdapter
{

    /**
     * @var ImageX ImageX Client Instance
     */
    private $client;

    /**
     * @var ImageXConfig ImageX Client Settings
     */
    private $config;


    public function __construct(ImageXConfig $config){
        $this->client = ImageX::getInstance($config->region);
        $this->client->setAccessKey($config->accessKey);
        $this->client->setSecretKey($config->secretKey);

        $this->config = $config;
    }

    /**
     * Generate the uri Prefix for deleting operation.
     *
     * @return string
     * @throws \Exception
     */
    function imageXBuildDeleteUriPrefix()
    {
        $prefix = '';
        switch ($this->config->region) {
            case 'cn-north-1':
                $prefix = 'tos-cn-i-';
                break;
            case 'us-east-1':
                $prefix = 'tos-us-i-';
                break;
            case 'ap-singapore-1':
                $prefix = 'tos-ap-i-';
                break;
            default:
                throw new \Exception(sprintf("ImageX not support region, %s", $this->config->region));
        }
        return $prefix. $this->config->serviceId;
    }


    public function fileExists(string $path): bool
    {
        // TODO: Implement fileExists() method.
        throw new NotImplementedException();
    }

    public function write(string $path, string $contents, Config $config): void
    {
        // Sign
        $applyParams = [];
        $applyParams["Action"] = "ApplyImageUpload";
        $applyParams["Version"] = "2018-08-01";
        $applyParams["ServiceId"] = $this->config->serviceId;
        $applyParams["UploadNum"] = 1;
        $applyParams["StoreKeys"] = array();
        $queryStr = http_build_query($applyParams);

        $queryStr = $queryStr . "&StoreKeys=" . $path;
        $response = $this->client->applyUploadImage(['query' => $queryStr]);

        $applyResponse = json_decode($response, true);
        if (isset($applyResponse["ResponseMetadata"]["Error"])) {
            throw new UnableToWriteFile(sprintf("uploadImages: request id %s error %s", $applyResponse["ResponseMetadata"]["RequestId"], $applyResponse["ResponseMetadata"]["Error"]["Message"]));
        }

        $uploadAddr = $applyResponse['Result']['UploadAddress'];
        if (count($uploadAddr['UploadHosts']) == 0) {
            throw new UnableToWriteFile("uploadImages: no upload host found");
        }
        $uploadHost = $uploadAddr['UploadHosts'][0];
        if (count($uploadAddr['StoreInfos']) != 1) {
            throw new UnableToWriteFile("uploadImages: store infos num != upload num");
        }

        // Upload
        $crc32 = dechex(crc32($contents));
        $tosClient = new Client([
            'base_uri' => "https://" . $uploadHost,
            'timeout' => 5.0,
        ]);
        $response = $tosClient->request('PUT',
            $uploadAddr['StoreInfos'][0]["StoreUri"],
            [   "body" => $contents,
                "headers" =>
                    ['Authorization' => $uploadAddr['StoreInfos'][0]["Auth"],
                    'Content-CRC32' => $crc32]
            ]);
        $uploadResponse = json_decode((string) $response->getBody(), true);
        if (!isset($uploadResponse["success"]) || $uploadResponse["success"] != 0) {
            throw new UnableToWriteFile("upload " . $path . " error");
        }

        // Commit
        $commitParams = [];
        $commitParams["ServiceId"] = $this->config->serviceId;
        $commitBody = [];
        $commitBody["SessionKey"] = $uploadAddr['SessionKey'];
        $commitReq = [
            "query" => $commitParams,
            "json" => $commitBody,
        ];

        $response = json_decode($this->client->commitUploadImage($commitReq), true);
        if (isset($response["ResponseMetadata"]["Error"])) {
            throw new UnableToWriteFile(sprintf("uploadImages: request id %s error %s", $response["ResponseMetadata"]["RequestId"], $response["ResponseMetadata"]["Error"]["Message"]));
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->write($path, stream_get_contents($contents), $config);
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
        $path = $this->imageXBuildDeleteUriPrefix() . '/' . $path;
        $response = json_decode($this->client->deleteImages($this->config->serviceId, [$path]), true);
        if (isset($response["ResponseMetadata"]["Error"])) {
            throw new UnableToDeleteFile(sprintf("deleteImages: request id %s error %s", $response["ResponseMetadata"]["RequestId"], $response["ResponseMetadata"]["Error"]["Message"]));
        }
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
