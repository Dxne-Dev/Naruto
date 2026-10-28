<?php

namespace App\Services\ImageGeneration;

use App\Models\Fighter;
use App\Services\ImageGeneration\Drivers\LocalFallbackDriver;
use App\Services\ImageGeneration\Drivers\MetaMuseDriver;

class ShinobiImageService
{
    public function __construct(
        protected MetaMuseDriver $museDriver,
        protected LocalFallbackDriver $fallbackDriver,
        protected ShinobiPromptBuilder $promptBuilder,
    ) {}

    /**
     * Generate or update the portrait for a shinobi.
     */
    public function generateForFighter(Fighter $fighter, ?string $sourcePhotoUrl = null): string
    {
        $driverName = config('services.image_generator.driver', 'meta_muse');

        $generator = match ($driverName) {
            'meta_muse' => $this->museDriver,
            default => $this->fallbackDriver,
        };

        $portraitUrl = $generator->generate($fighter, $sourcePhotoUrl);

        $fighter->update([
            'portrait_path' => $portraitUrl,
        ]);

        return $portraitUrl;
    }

    /**
     * Retrieve the constructed prompt for preview/debugging.
     */
    public function previewPrompt(Fighter $fighter): array
    {
        return [
            'prompt' => $this->promptBuilder->buildPrompt($fighter),
            'negative_prompt' => $this->promptBuilder->buildNegativePrompt(),
        ];
    }
}
