<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Guard: fail fast if incoming request contains keys not present in table columns.
     * Allows extra keys via $allowedExtras (e.g. _token, _method, files) and arbitrary list.
     */
    protected function guardUnknownFields(string $table, array $allowedExtras = ['_token', '_method']): void
    {
        if (! App::environment('local', 'testing')) {
            return;
        }

        $incomingKeys = array_keys($this->all());
        $knownColumns = Schema::getColumnListing($table);

        $unknown = array_values(array_diff($incomingKeys, array_merge($knownColumns, $allowedExtras)));

        if (! empty($unknown)) {
            throw ValidationException::withMessages([
                '__unknown' => [
                    'Unknown input fields: ' . implode(', ', $unknown) . ' (table: ' . $table . ')',
                ],
            ]);
        }
    }
}


