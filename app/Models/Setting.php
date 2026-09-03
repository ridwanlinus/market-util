<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['company_id', 'key', 'value'];

    public static function get(string $key, ?int $companyId = null, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->where('company_id', $companyId)->first();

        if (! $row || $row->value === null) {
            return $default;
        }

        // Nilai yang disimpan dengan encrypt: prefix.
        if (str_starts_with($row->value, 'enc:')) {
            try {
                return Crypt::decryptString(substr($row->value, 4));
            } catch (\Throwable) {
                return $default;
            }
        }

        return $row->value;
    }

    public static function put(string $key, mixed $value, ?int $companyId = null, bool $encrypt = false): void
    {
        $value = $encrypt && $value !== null ? 'enc:' . Crypt::encryptString((string) $value) : $value;

        static::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => $value]
        );
    }
}