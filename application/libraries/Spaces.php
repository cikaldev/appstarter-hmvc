<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Aws\S3\S3Client;

class Spaces
{
  protected $ci;
  protected $client;
  private   $data;

  public function __construct($config=array())
  {
    $this->ci =& get_instance();
    if (empty($config)) show_error('No config present!', 400, 'Spaces');

    $this->client = new S3Client($config);
  }

  public function getListBucket()
  {
    $this->data = $this->client->listBuckets();
    return $this;
  }

  public function getListFileFromBucket(string $bucketName)
  {
    $this->data = $this->client->listObjects([
      'Bucket' => $bucketName,
    ]);

    return $this;
  }

  public function downloadFileFromBucket(string $bucketName, string $fileName, string $fileTarget)
  {
    $result = $this->client->getObject([
      'Bucket' => $bucketName,
      'Key' => $fileName,
    ]);
    
    file_put_contents($fileTarget, $result['Body']);

    $this->data = [
      'success' => file_exists($fileTarget),
      'result' => [
        'bucket' => $bucketName,
        'get' => $fileName,
        'put' => $fileTarget,
      ]
    ];

    return $this;
  }

  public function uploadToBucket(string $bucketName, string $fileName, string $localFile, string $acl='public-read', array $metadata=[])
  {
    if (!isset($metadata['Content-Type'])) {
      $metadata['Content-type'] = mime_content_type($localFile);
    }

    $this->data = $this->client->putObject([
      'Bucket'     => $bucketName,
      'Key'        => $fileName,
      'SourceFile' => $localFile,
      'ACL'        => $acl,
      'Metadata'   => $metadata
    ]);

    return $this;
  }

  public function deleteFileFromBucket(string $bucketName, string $fileName)
  {
    $this->client->deleteObject([
      'Bucket' => $bucketName,
      'Key' => $fileName
    ]);
    
    $this->data = [
      'success' => true,
      'result' => [
        'bucket' => $bucketName,
        'del' => $fileName,
      ]
    ];

    return $this;
  }

  public function deleteBucket(string $bucketName)
  {
    $this->data = $this->client->deleteBucket(['Bucket' => $bucketName]);
    return $this;
  }
  
  // ------------------------------------------------------------------------
  // Custom Utilities
  // ------------------------------------------------------------------------
  public function find($fileName)
  {
    if (is_null($this->data)) {
      show_error('Cannot find filename from NULL data', 400, 'Spaces');
    }

    $data = $this->data->toArray();
    $result = [];
    foreach ($data['Contents'] as $v) {
      if (stripos($v['Key'], $fileName) !== false) {
        $result[] = $v;
      }
    }
    $this->data['Contents'] = $result;
    return $this;
  }

  public function result()
  {
    return $this->data;
  }

  public function toArray()
  {
    return $this->data->toArray();
  }

  public function toObject()
  {
    $data = json_decode(json_encode($this->data->toArray()));
    return $data;
  }

  public function toJSON()
  {
    $result = [];
    try {
      $result = $this->data->toArray();
    } catch(Throwable $t) {
      $result = $this->data;
    } finally {
      $this->ci->output
        ->set_content_type('application/json')
        ->set_output(json_encode($result));
    }
  }

}

/* End of file Spaces.php */
/* Location: ./application/libraries/Spaces.php */
