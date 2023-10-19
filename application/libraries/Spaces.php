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

    if (!empty($config)) {
      $this->client = new S3Client($config);
    } else {
      $this->ci->config->load('spaces', false, true);
      $options = $this->ci->config->item('SPACES_OPTION');
      $this->client = new S3Client($options);
    }
  }

  public function getListBucket()
  {
    $this->data = $this->client->listBuckets();
    return $this;
  }

  public function getListFileFromBucket(string $bucketName, string $marker='')
  {
    $this->data = $this->client->listObjects([
      'Bucket' => $bucketName,
      'Marker' => $marker,
    ]);

    return $this;
  }

  public function getAllListFileFromBucket(string $bucketName)
  {
    $data = [];
    $size = 0;
    $temp = $this->getListFileFromBucket($bucketName)->toArray();
    foreach ($temp['Contents'] as $val) {
      $data[] = [
        'Key' => $val['Key'],
        'LastModified' => $val['LastModified'],
        'Size' => $this->sizeFilter((int)$val['Size']),
        'ETag' => preg_replace("/[^a-zA-Z0-9]+/", "", $val['ETag']),
      ];
      $size += (int)$val['Size'];
    }

    if ($temp['IsTruncated']) {
      do {
        $temp = $this->getListFileFromBucket($bucketName, $temp['NextMarker'])->toArray();
        foreach ($temp['Contents'] as $val) {
          $data[] = [
            'Key' => $val['Key'],
            'LastModified' => $val['LastModified'],
            'Size' => $this->sizeFilter((int)$val['Size']),
            'ETag' => preg_replace("/[^a-zA-Z0-9]+/", "", $val['ETag']),
          ];
          $size += (int)$val['Size'];
        }
      } while($temp['IsTruncated']);
    }

    $this->data = [
      'bucket' => [
        'name' => $bucketName,
        'size' => $this->sizeFilter($size),
        'items' => count($data),
      ],
      'items' => $data,
    ];
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

  private function sizeFilter($bytes)
  {
    $label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $label ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $label[$i] );
  }

}

/* End of file Spaces.php */
/* Location: ./application/libraries/Spaces.php */
