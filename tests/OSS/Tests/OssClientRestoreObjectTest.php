<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\OssClient;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';


class OssClientRestoreObjectTest extends TestOssClientBase
{
    private $IABucket;
    private $ArchiveBucket;

    public function testIARestoreObject()
    {
        $object = 'storage-object';

        $this->ossClient->putObject($this->IABucket, $object,'testcontent');
        try{
            $this->ossClient->restoreObject($this->IABucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('400', $e->getHTTPStatus());
            $this->assertEquals('OperationNotSupported', $e->getErrorCode());
        }
    }

    public function testNullObjectRestoreObject()
    {
        $object = 'null-object';

        try{
            $this->ossClient->restoreObject($this->bucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('404', $e->getHTTPStatus());
        }
    }

    public function testArchiveRestoreObject()
    {
        $object = 'storage-object';

        $this->ossClient->putObject($this->ArchiveBucket, $object,'testcontent');
        try{
            $this->ossClient->getObject($this->ArchiveBucket, $object);
            $this->assertTrue(false);
        }catch (OssException $e){
            $this->assertEquals('403', $e->getHTTPStatus());
            $this->assertEquals('InvalidObjectState', $e->getErrorCode());
        }
        $result = $this->ossClient->restoreObject($this->ArchiveBucket, $object);
        common::waitMetaSync();
        $this->assertEquals('202', $result['info']['http_code']);

        try{
            $this->ossClient->restoreObject($this->ArchiveBucket, $object);
        }catch(OssException $e){
            $this->assertEquals('409', $e->getHTTPStatus());
            $this->assertEquals('RestoreAlreadyInProgress', $e->getErrorCode());
        }
    }

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->IABucket = 'ia-'.$this->bucket;
        $this->ArchiveBucket = 'archive-'.$this->bucket;
        $this->ossClient->createBucket($this->IABucket, OssClient::OSS_ACL_TYPE_PRIVATE, NULL, OssClient::OSS_STORAGE_TYPE_IA);
        $this->ossClient->createBucket($this->ArchiveBucket, OssClient::OSS_ACL_TYPE_PRIVATE, NULL, OssClient::OSS_STORAGE_TYPE_ARCHIVE);
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $object = 'storage-object';
        $this->ossClient->deleteObject($this->IABucket, $object);
        $this->ossClient->deleteObject($this->ArchiveBucket, $object);
        $this->ossClient->deleteBucket($this->IABucket);
        $this->ossClient->deleteBucket($this->ArchiveBucket);
    }
}



