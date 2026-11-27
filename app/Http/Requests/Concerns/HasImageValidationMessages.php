<?php

namespace App\Http\Requests\Concerns;

trait HasImageValidationMessages
{
    /**
     * @return array<string, string>
     */
    public function imageMessages(
        string $field = 'image',
        string $types = 'JPG, PNG, or WebP',
        int $maxMb = 5,
    ): array {
        return [
            "{$field}.image" => 'The selected file must be a valid image.',
            "{$field}.mimes" => "Use a {$types} file.",
            "{$field}.max" => "The file is too large. Choose a file of {$maxMb} MB or less.",
            "{$field}.uploaded" => 'The file could not be uploaded. Please try again.',
        ];
    }
}
