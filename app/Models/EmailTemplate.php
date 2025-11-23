<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'html_content',
        'text_content',
        'variables',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get default template for a type
     */
    public static function getDefault(string $type): ?self
    {
        return self::where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Render template with variables
     */
    public function render(array $variables = []): string
    {
        $content = $this->html_content;
        
        foreach ($variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }
        
        return $content;
    }
}

