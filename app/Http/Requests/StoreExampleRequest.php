<?php

namespace App\Http\Requests;

class StoreExampleRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        // Guard unknown inputs against the target table (adjust table name per model)
        $this->guardUnknownFields('examples', ['_token', '_method']);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}


