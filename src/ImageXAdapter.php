<?php

namespace ExerciseBook\Flysystem\ImageX;

use ExerciseBook\Flysystem\ImageX\Exception\NotImplementedException;
use GuzzleHttp\Client;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
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

    /**
     * @var string Resources uri Prefix
     */
    private $uriPrefix;


    public function __construct(ImageXConfig $config){
        $this->client = ImageX::getInstance($config->region);
        $this->client->setAccessKey($config->accessKey);
        $this->client->setSecretKey($config->secretKey);

        $this->config = $config;
        $this->uriPrefix = $this->imageXBuildUriPrefix();
    }

    /**
     * Generate the uri Prefix
     *
     * @return string
     * @throws \Exception
     */
    function imageXBuildUriPrefix()
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

    private function getFileMetadata(string $path) {
        $path = $this->uriPrefix . '/' . $path;
        $response = json_decode($this->client->getImageUploadFile($this->config->serviceId, $path), true);

        if (isset($response["ResponseMetadata"]["Error"])) {
            $error = $response["ResponseMetadata"]["Error"];
            if ($error['CodeN'] == 604006) {
                throw UnableToRetrieveMetadata::create($path, "any", $error['Message']);
            } else {
                throw UnableToCheckFileExistence::forLocation($path);
            }
        }

        $data = $response['Result'];
        return new FileAttributes($data['FileName'], $data['FileSize'], null, strtotime($data['LastModified']), null, $data);
    }

    public function fileExists(string $path): bool
    {
        try {
            $response = $this->getFileMetadata($path);
        } catch (UnableToRetrieveMetadata $e) {
            return false;
        }
        return true;
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
        if (!$this->fileExists($path)) {
            throw UnableToReadFile::fromLocation($path);
        }

        $httpClient = new Client();
        $url = $this->config->domain. '/'. $this->uriPrefix. '/'. $path;
        return $httpClient->get($url)->getBody()->getContents();
    }

    public function readStream(string $path)
    {
        if (!$this->fileExists($path)) {
            throw UnableToReadFile::fromLocation($path);
        }

        $httpClient = new Client();
        $url = $this->config->domain. '/'. $this->uriPrefix. '/'. $path;
        return $httpClient->get($url)->getBody()->detach();
    }

    public function delete(string $path): void
    {
        $path = $this->uriPrefix . '/' . $path;
        $response = json_decode($this->client->deleteImages($this->config->serviceId, [$path]), true);
        if (isset($response["ResponseMetadata"]["Error"])) {
            throw new UnableToDeleteFile(sprintf("deleteImages: request id %s error %s", $response["ResponseMetadata"]["RequestId"], $response["ResponseMetadata"]["Error"]["Message"]));
        }
    }

    public function visibility(string $path): FileAttributes
    {
        return $this->getFileMetadata($path);
//        throw UnableToRetrieveMetadata::visibility($path, error_get_last()['message'] ?? '');
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->getFileMetadata($path);
//        throw UnableToRetrieveMetadata::mimeType($path, error_get_last()['message'] ?? '');
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->getFileMetadata($path);
//        throw UnableToRetrieveMetadata::lastModified($path, error_get_last()['message'] ?? '');
    }

    public function fileSize(string $path): FileAttributes
    {
        return $this->getFileMetadata($path);
//        throw UnableToRetrieveMetadata::fileSize($path, error_get_last()['message'] ?? '');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $response = json_decode($this->client->getImageUploadFiles($this->config->serviceId, $path), true);

        return null;
    }

    public function move(string $source, string $destination, Config $config): void
    {
        // There is no move operation for ImageX so far
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        // There is no copy operation for ImageX so far
    }

    public function deleteDirectory(string $path): void
    {
        // There is no directory operation for ImageX so far
    }

    public function createDirectory(string $path, Config $config): void
    {
        // There is no directory operation for ImageX so far
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // There is no visibility operation for ImageX so far
    }
}
