<?php

require HAMNAGHSHEH_DIR . 'aws/aws-autoloader.php';
use Aws\S3\S3Client;

class Hamnaghsheh_Minio {

    private static $instance = null;

    private $endpoint = 's3.ir-thr-at1.arvanstorage.ir';
    private $access   = '579536ef-2b3d-4e32-b4b6-5bd11a411eec';
    private $secret   = '71303dbbbd6f3f813b006459505ed5236e2f98fe33361ceadc3803a8f4a1e31a';
    private $bucket   = 'hamaghsheh-storage';
    private $use_ssl  = true;

    // private $endpoint = 's3.greenplus.cloud:9001';
    // private $access   = 'gp-client1';
    // private $secret   = 'NtiW20Mlu37M2A9lZTl65rjCnkIX';
    // private $bucket   = 'gp-client1';
    // private $use_ssl  = true;

    private $client = null;

    private function __construct() {
        $this->init_client();
    }

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_client() {
        $proto = $this->use_ssl ? 'https://' : 'http://';

        $this->client = new S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'endpoint' => $proto . $this->endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $this->access,
                'secret' => $this->secret,
            ]
        ]);
    }

    // متد اپلود فایل
    public function upload($local_path, $original_name) {
        if (!file_exists($local_path)) {
            return [
                'success' => false,
                'error'   => 'فایل لوکال یافت نشد'
            ];
        }
    
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique = uniqid('file_') . '_' . time() . ($ext ? '.' . $ext : '');

        try {
            $result = $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $unique,
                'SourceFile' => $local_path,
                'ACL' => 'public-read'
            ]);
            return [
                'success' => true,
                'url'     => isset($result['ObjectURL']) ? $result['ObjectURL'] : null,
                'key'    => isset($result['ObjectURL']) ? $unique : null,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
    
    public function delete($key) {
        if (empty($key)) {
            return [
                'success' => false,
                'error'   => 'کي فايل ارسال نشده است'
            ];
        }
    
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);
    
            return [
                'success' => true,
                'message' => 'فايل حذف شد'
            ];
    
        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }

}



