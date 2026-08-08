<?php

declare(strict_types=1);

/**
 * Orangescrum Community Edition
 *
 * Copyright (c) 2026 Andolasoft Inc.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Controller\Component;

use Aws\S3\S3Client;
use Cake\Controller\Component;
use Cake\Core\Configure;

class StorageComponent extends Component
{
    protected ?S3Client $s3;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Required storage configuration keys
        $requiredConfigs = ['region', 'endpoint', 'accessKey', 'secretKey', 'bucket'];
        $missingConfigs = [];

        foreach ($requiredConfigs as $key) {
            if (empty(Configure::read("Storage.$key"))) {
                $missingConfigs[] = $key;
            }
        }

        if (!empty($missingConfigs)) {
            // Mark component as inactive
            $this->s3 = null;
            return;
        }

        // Initialize S3 client
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => Configure::read('Storage.region'),
            'endpoint' => Configure::read('Storage.endpoint'),
            'use_path_style_endpoint' => Configure::read('Storage.use_path_style_endpoint') ?? false,
            'credentials' => [
                'key' => Configure::read('Storage.accessKey'),
                'secret' => Configure::read('Storage.secretKey'),
            ],
        ]);
    }


    public function uploadFile(string $filePath, string $fileName): ?string
    {
        if ($this->s3 === null) {
            return null; // S3 client is not initialized
        }

        $bucket = Configure::read('Storage.bucket');

        try {
            $result = $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
                'SourceFile' => $filePath,
                'ACL' => 'public-read',
            ]);

            return $result['ObjectURL']; // Returns the public URL of the file
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return null; // Return null if the upload fails
        }
    }

    public function getFile(string $fileName): ?string
    {
        if ($this->s3 === null) {
            return null; // S3 client is not initialized
        }

        $bucket = Configure::read('Storage.bucket');

        try {
            $result = $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
            ]);

            return (string) $result['Body']; // Returns the file content
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return null;
        }
    }

    public function headObject(string $fileName): ?array
    {
        if ($this->s3 === null) {
            return null; // S3 client is not initialized
        }

        $bucket = Configure::read('Storage.bucket');

        try {
            $result = $this->s3->headObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
            ]);

            return $result->toArray(); // Returns metadata of the object
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return null;
        }
    }

    public function generateTemporaryURL(string $fileName, ?string $bucketName = null, int $expiresIn = 86400): string
    {
        if ($this->s3 === null) {
            return ''; // S3 client is not initialized
        }

        $bucket = $bucketName ?? Configure::read('Storage.bucket');

        $command = $this->s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $fileName,
        ]);

        $request = $this->s3->createPresignedRequest($command, '+' . $expiresIn . ' seconds');

        return (string) $request->getUri(); // Returns the presigned URL
    }

    public function removeFile(string $fileName): bool
    {
        if ($this->s3 === null) {
            return false; // S3 client is not initialized
        }

        $bucket = Configure::read('Storage.bucket');

        try {
            $this->s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
            ]);

            return true; // File successfully deleted
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return false; // File deletion failed
        }
    }

    public function copyFile(string $sourceFileName, string $destinationFileName, ?string $sourceBucket = null, ?string $destinationBucket = null): bool
    {
        if ($this->s3 === null) {
            return false; // S3 client is not initialized
        }

        $sourceBucket = $sourceBucket ?? Configure::read('Storage.bucket');
        $destinationBucket = $destinationBucket ?? Configure::read('Storage.bucket');

        try {
            $this->s3->copyObject([
                'Bucket' => $destinationBucket,
                'Key' => $destinationFileName,
                'CopySource' => "{$sourceBucket}/{$sourceFileName}",
                'ACL' => 'public-read',
            ]);

            return true; // File successfully copied
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return false; // File copy failed
        }
    }

    public function getObjectInfo(string $fileName): ?array
    {
        if ($this->s3 === null) {
            return null; // S3 client is not initialized
        }

        $bucket = Configure::read('Storage.bucket');

        try {
            $result = $this->s3->headObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
            ]);

            return [
                'ContentLength' => $result['ContentLength'] ?? null,
                'ContentType' => $result['ContentType'] ?? null,
                'LastModified' => $result['LastModified'] ?? null,
                'ETag' => $result['ETag'] ?? null,
                'type' => $result['@metadata']['type'] ?? null,
            ]; // Returns basic information about the object
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return null;
        }
    }

    public function pub_file_exists($folder, $fileName)
    {
        if ($this->s3 === null) {
            return false; // S3 client is not initialized
        }

        return $this->getObjectInfo($folder . $fileName);
    }

    public function deleteObject(string $fileName): bool
    {
        if ($this->s3 === null) {
            return false; // S3 client is not initialized
        }

        $bucket = Configure::read('Storage.bucket');

        try {
            $this->s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $fileName,
            ]);

            return true; // Object successfully deleted
        } catch (\Aws\Exception\AwsException $e) {
            // Log the error or handle it as needed
            return false; // Object deletion failed
        }
    }


}
