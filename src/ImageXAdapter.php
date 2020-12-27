<?php

namespace ExerciseBook\Flysystem\ImageX;

use ExerciseBook\Flysystem\ImageX\Exception\NotImplementedException;
use GuzzleHttp\Client;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToWriteFile;
use Volc\Service\ImageX;

class ImageXAdapter implements FilesystemAdapter
{

    /**
     * @var ImageX
     */
    private $client;

    private $serviceId;

    public function __construct(string $region, string $accessKey, string $secretKey, string $serviceId){
        $this->client = ImageX::getInstance($region);
        $this->client->setAccessKey($accessKey);
        $this->client->setSecretKey($secretKey);
        $this->serviceId = $serviceId;
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
        $applyParams["ServiceId"] = $this->serviceId;
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
        $commitParams["ServiceId"] = $this->serviceId;
        $commitBody = [];
        $commitBody["SessionKey"] = $uploadAddr['SessionKey'];
        $commitReq = [
            "query" => $commitParams,
            "json" => $commitBody,
        ];
        $response = $this->client->commitUploadImage($commitReq);
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
