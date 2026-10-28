<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpTournamentQuota extends Model
{
    protected $fillable = [
        'ip_address',
        'device_fingerprint',
        'user_agent',
        'free_participations_count',
    ];

    protected $casts = [
        'free_participations_count' => 'integer',
    ];

    /**
     * Generate a deterministic 64-char SHA-256 device fingerprint combining IP and User-Agent.
     */
    public static function generateFingerprint(?string $ip, ?string $userAgent): string
    {
        $ip = trim((string) $ip) ?: '0.0.0.0';
        $ua = trim((string) $userAgent) ?: 'unknown';

        return hash('sha256', $ip.'|'.$ua);
    }

    /**
     * Record a free participation for given IP and User-Agent device fingerprint.
     */
    public static function incrementUsage(?string $ip, ?string $userAgent = null): void
    {
        if (! $ip && ! $userAgent) {
            return;
        }

        $fingerprint = static::generateFingerprint($ip, $userAgent);

        $record = static::where('device_fingerprint', $fingerprint)->first()
            ?: ($ip ? static::where('ip_address', $ip)->whereNull('device_fingerprint')->first() : null);

        if ($record) {
            $record->update([
                'ip_address' => $ip ?: $record->ip_address,
                'device_fingerprint' => $fingerprint,
                'user_agent' => $userAgent ?: $record->user_agent,
                'free_participations_count' => $record->free_participations_count + 1,
            ]);
        } else {
            static::create([
                'ip_address' => $ip,
                'device_fingerprint' => $fingerprint,
                'user_agent' => $userAgent,
                'free_participations_count' => 1,
            ]);
        }
    }

    /**
     * Check if the device fingerprint or IP has reached the 2 free tournament limit.
     */
    public static function hasReachedLimit(?string $ip, ?string $userAgent = null): bool
    {
        if (! $ip && ! $userAgent) {
            return false;
        }

        $fingerprint = static::generateFingerprint($ip, $userAgent);

        // 1. Primary check by device fingerprint (combining IP + User-Agent)
        $record = static::where('device_fingerprint', $fingerprint)->first();
        if ($record && $record->free_participations_count >= 2) {
            return true;
        }

        // 2. Fallback check for legacy records without fingerprint
        if ($ip) {
            $legacyRecord = static::where('ip_address', $ip)
                ->whereNull('device_fingerprint')
                ->first();
            if ($legacyRecord && $legacyRecord->free_participations_count >= 2) {
                return true;
            }
        }

        return false;
    }
}
