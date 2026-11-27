<?php

namespace App\Http\Requests\Concerns;

trait ExplainsUploadErrors
{
    /** @return array<string, string> */
    protected function uploadMessages(): array
    {
        return [
            'image.image' => 'The selected file is not a valid image. Choose a JPG, PNG, or WebP file.',
            'image.mimes' => 'This image format is not supported. Upload a JPG, PNG, or WebP file.',
            'image.uploaded' => 'The image could not be uploaded. Check the file and try again.',
            'image.max' => 'The image is larger than 5 MB. Choose a smaller file and try again.',
        ];
    }
}
