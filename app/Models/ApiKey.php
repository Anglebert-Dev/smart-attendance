<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = ['name', 'key_hash', 'plain_key', 'is_active', 'last_used_at'];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Generate a new API key pair.
     * Returns the plain-text key (shown once) and the saved record.
     */
    public static function generate(string $name): array
    {
        $plainKey = 'sas_' . bin2hex(random_bytes(32)); // e.g. sas_a1b2c3...
        $hash     = hash('sha256', $plainKey);

        $record = self::create([
            'name'      => $name,
            'key_hash'  => $hash,
            'plain_key' => $plainKey, // stored temporarily, cleared after first view
        ]);

        return ['record' => $record, 'plain_key' => $plainKey];
    }

    /**
     * Verify an incoming plain-text key.
     * Returns the matching active ApiKey or null.
     * Side-effects: updates last_used_at, clears stored plain_key.
     */
    public static function verify(string $plainKey): ?self
    {
        $hash = hash('sha256', $plainKey);

        $key = self::where('key_hash', $hash)
                   ->where('is_active', true)
                   ->first();

        if ($key) {
            $key->update([
                'last_used_at' => now(),
                'plain_key'    => null, // wipe stored plain text after first real use
            ]);
        }

        return $key;
    }
}
