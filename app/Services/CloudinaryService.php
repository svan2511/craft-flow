<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Throwable;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
        ]);
    }

    /**
     * Upload a single base64 data-URI (e.g. data:image/jpeg;base64,...) to Cloudinary.
     */
    public function uploadDataUri(string $dataUri): ?string
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($dataUri, [
                'folder' => config('services.cloudinary.folder'),
                'resource_type' => 'image',
            ]);

            return $result['secure_url'] ?? null;
        } catch (Throwable $e) {
            Log::error('Cloudinary upload failed: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Upload multiple base64 data-URIs. Returns only successfully uploaded URLs.
     *
     * @param  string[]  $dataUris
     * @return string[]
     */
    public function uploadMany(array $dataUris): array
    {
        $urls = [];
        foreach ($dataUris as $dataUri) {
            $url = $this->uploadDataUri($dataUri);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls;
    }
}
