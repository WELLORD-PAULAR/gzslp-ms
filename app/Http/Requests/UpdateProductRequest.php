<?php

namespace App\Http\Requests;

<<<<<<< HEAD
use App\Http\Requests\Concerns\ExplainsUploadErrors;
=======
use App\Http\Requests\Concerns\HasImageValidationMessages;

>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
<<<<<<< HEAD
    use ExplainsUploadErrors;
=======
    use HasImageValidationMessages;
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shop') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['sometimes', Rule::in(\App\Models\Product::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'paper_size' => ['nullable', 'string', 'max:80'],
            'paper_weight' => ['nullable', 'string', 'max:80'],
            'paper_finish' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'lock_version' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

<<<<<<< HEAD
    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->uploadMessages();
=======
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages();
>>>>>>> 5a919e28acce8756fd5f3c90182d2171bfa4ec11
    }
}
