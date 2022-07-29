<?php

namespace ExerciseBook\Flysystem\ImageX;

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

    public function __construct($config)
    {
        if ($config instanceof ImageXConfig) {
            $this->client = ImageX::getInstance($config->region);
            $this->client->setAccessKey($config->accessKey);
            $this->client->setSecretKey($config->secretKey);

            $this->config = $config;
            $this->uriPrefix = $this->imageXBuildUriPrefix();
        } else if (is_array($config)) {
            $this->config = new ImageXConfig();

            $this->config->region = $config["region"];
            $this->config->accessKey = $config["access_key"];
            $this->config->secretKey = $config["secret_key"];
            $this->config->serviceId = $config["service_id"];
            $this->config->domain = $config["domain"];

            $this->client = ImageX::getInstance($this->config->region);
            $this->client->setAccessKey($this->config->accessKey);
            $this->client->setSecretKey($this->config->secretKey);

            $this->uriPrefix = $this->imageXBuildUriPrefix();
        } else throw new \InvalidArgumentException("Config not supported.");
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
        return $prefix . $this->config->serviceId;
    }

    private function arrayGetDefault($arr, $key, $default = null)
    {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        }
        return $default;
    }

    /**
     * ImageX Interface getImageUploadFiles
     *
     * @param string|null $fNamePrefix
     * @param int $offset
     * @param int $limit
     * @param int $marker
     * @return string
     */
    public function getImageUploadFiles(string $fNamePrefix = null, int $offset = 0, int $limit = 1, int $marker = 0)
    {
        $applyParams = [];
        $applyParams["Action"] = "GetImageUploadFiles";
        $applyParams["Version"] = "2018-08-01";
        $applyParams["ServiceId"] = $this->config->serviceId;

        if ($fNamePrefix != null) $applyParams["FnamePrefix"] = $fNamePrefix;
        $applyParams["Offset"] = $offset;
        $applyParams["Limit"] = $limit;
        $applyParams["Marker"] = $marker;

        $queryStr = http_build_query($applyParams);

        return $response = $this->client->requestImageX('GetImageUploadFiles', ['query' => $queryStr]);
    }

    /**
     * ImageX Interface getImageUploadFile
     *
     * @param string|null $storeUri
     * @return string
     */
    public function getImageUploadFile(string $storeUri = null)
    {
        $applyParams = [];
        $applyParams["Action"] = "GetImageUploadFile";
        $applyParams["Version"] = "2018-08-01";
        $applyParams["ServiceId"] = $this->config->serviceId;

        $applyParams["StoreUri"] = $storeUri;

        $queryStr = http_build_query($applyParams);

        return $response = $this->client->requestImageX('GetImageUploadFile', ['query' => $queryStr]);
    }

    /**
     * FlySystem metadata helper
     *
     * @param string $path
     * @return FileAttributes
     */
    private function getFileMetadata(string $path)
    {
        $path = $this->uriPrefix . '/' . $path;
        $response = json_decode($this->getImageUploadFile($path), true);

        if (isset($response["ResponseMetadata"]["Error"])) {
            $error = $response["ResponseMetadata"]["Error"];
            if ($error['CodeN'] == 604006) {
                throw UnableToRetrieveMetadata::create($path, "any", $error['Message']);
            } else {
                throw UnableToCheckFileExistence::forLocation($path);
            }
        }

        $data = $response['Result'];
        return new FileAttributes($data['StoreUri'], $data['FileSize'], null, strtotime($data['LastModified']), null, $data);
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
            ["body" => $contents,
                "headers" =>
                    ['Authorization' => $uploadAddr['StoreInfos'][0]["Auth"],
                        'Content-CRC32' => $crc32]
            ]);
        $uploadResponse = json_decode((string)$response->getBody(), true);
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
        $url = $this->config->domain . '/' . $this->uriPrefix . '/' . $path;
        return $httpClient->get($url)->getBody()->getContents();
    }

    public function readStream(string $path)
    {
        if (!$this->fileExists($path)) {
            throw UnableToReadFile::fromLocation($path);
        }

        $httpClient = new Client();
        $url = $this->config->domain . '/' . $this->uriPrefix . '/' . $path;
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
        $path = trim($path, '/\\');

        $continue = true;
        $offset = 0;
        while ($continue) {
            $response = json_decode($this->getImageUploadFiles($path, $offset, 100, 0), true);

            if (isset($response["ResponseMetadata"]["Error"])) {
                break;
            }

            $result = $response['Result'];

            $fileObjects = $result['FileObjects'];
            foreach ($fileObjects as $data) {
                yield new FileAttributes($data['StoreUri'], $data['FileSize'], null, strtotime($data['LastModified']), null, $data);
            }

            $offset += 100;
            $continue = $result['hasMore'];
        }

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
