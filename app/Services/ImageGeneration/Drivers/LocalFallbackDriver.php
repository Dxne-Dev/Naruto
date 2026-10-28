<?php

namespace App\Services\ImageGeneration\Drivers;

use App\Models\Fighter;
use App\Services\ImageGeneration\Contracts\ShinobiImageGeneratorInterface;

class LocalFallbackDriver implements ShinobiImageGeneratorInterface
{
    /**
     * Curated thematic character portraits based on clan or elements.
     */
    protected const THEMED_PORTRAITS = [
        'Uchiha' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=85',
        'Hyūga' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=85',
        'Senju' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=600&auto=format&fit=crop&q=85',
        'Nara' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=600&auto=format&fit=crop&q=85',
        'Akimichi' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?w=600&auto=format&fit=crop&q=85',
        'Yamanaka' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop&q=85',
        'Aburame' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=600&auto=format&fit=crop&q=85',
        'Uzumaki' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=600&auto=format&fit=crop&q=85',
    ];

    public function generate(Fighter $fighter, ?string $sourcePhotoUrl = null): string
    {
        // If a valid custom photo URL is passed and already hosted or valid data-URI, return it
        if ($sourcePhotoUrl && (filter_var($sourcePhotoUrl, FILTER_VALIDATE_URL) || str_starts_with($sourcePhotoUrl, 'data:image/'))) {
            return $sourcePhotoUrl;
        }

        // Return themed portrait matching clan, or default
        return self::THEMED_PORTRAITS[$fighter->clan]
            ?? 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=600&auto=format&fit=crop&q=85';
    }
}
