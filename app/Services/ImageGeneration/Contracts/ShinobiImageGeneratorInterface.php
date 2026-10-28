<?php

namespace App\Services\ImageGeneration\Contracts;

use App\Models\Fighter;

interface ShinobiImageGeneratorInterface
{
    /**
     * Generate a character portrait for the given fighter.
     * Returns the public URL or relative path of the generated image.
     */
    public function generate(Fighter $fighter, ?string $sourcePhotoUrl = null): string;
}
