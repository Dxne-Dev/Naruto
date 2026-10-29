<?php

namespace App\Services\ImageGeneration;

use App\Models\Fighter;

class ShinobiPromptBuilder
{
    /**
     * Specific iconic visual cues for each clan.
     */
    protected const CLAN_VISUALS = [
        'Uchiha' => 'sleek raven dark hair, high-collared navy blue shinobi vest with Uchiha fan crest, glowing red Sharingan eyes with sharp tomoe marks',
        'Hyūga' => 'pure lavender-white Byakugan eyes with subtle bulging chakra veins near temples, traditional white martial arts robes, Gentle Fist stance',
        'Senju' => 'earthy forest green haori coat, giant sealing scroll strapped across the back, formidable resolute gaze, subtle wooden Mokuton vine details',
        'Nara' => 'high spiky ponytail hairstyle, mesh chainmail undershirt, dark shinobi flak jacket, lazy tactical piercing gaze, sharp shadows stretching from boots',
        'Akimichi' => 'traditional warrior armor plates emblazoned with the iconic food kanji, crimson swirling facial markings, powerful sturdy muscular build',
        'Yamanaka' => 'high blonde ponytail, elegant violet and lavender shinobi tunic, sharp turquoise eyes, hands forming the Mind Body Switch seal',
        'Aburame' => 'high upturned collar coat, round dark tinted spectacles concealing the eyes, mysterious shadowy hood, faint silhouette of Kikaichū beetles around fingers',
        'Uzumaki' => 'vibrant scarlet red hair, signature whisker marks or resilient determined grin, spiral vortex crest of Whirlpool Country on attire',
    ];

    /**
     * Village headband metal engravings and attire hints.
     */
    protected const VILLAGE_VISUALS = [
        'Konoha' => 'Hidden Leaf Village ninja forehead protector headband with metallic Leaf insignia, Leaf Jonin/Chūnin style tactical vest',
        'Kiri' => 'Hidden Mist Village ninja headband with engraved wave ripples, striped arm warmers, dual-layered mist shinobi attire',
        'Suna' => 'Hidden Sand Village ninja headband with stylized hourglass mark, desert windproof sand-colored cloak and breathable linen wraps',
        'Kumo' => 'Hidden Cloud Village ninja headband with three swirling clouds, white one-strap shoulder flak vest, lightning motifs',
        'Iwa' => 'Hidden Stone Village ninja headband with stone crest, earthy dark red and brown rock-shinobi uniform with single-sleeved mesh',
    ];

    /**
     * Aura effects based on the primary and secondary elements.
     */
    protected const ELEMENT_VISUALS = [
        'Katon' => 'faint crimson and golden flame embers crackling around the fists',
        'Fūton' => 'subtle swirling gusts of cutting green wind vortexes spiraling around the limbs',
        'Raiton' => 'intense jagged blue-white electric sparks discharging along the forearms',
        'Doton' => 'dense jagged stone armor fragments and levitating earthen particles near the boots',
        'Suiton' => 'crystalline water droplets and miniature whirlpool ripples flowing around the hands',
    ];

    /**
     * Combat specialization visual cues for prompt generation.
     */
    protected const SPEC_VISUALS = [
        'Ninjutsu' => 'specialized in elemental Ninjutsu hand seals',
        'Taijutsu' => 'dynamic martial arts hand-to-hand combat stance, taped knuckles and agility wraps',
        'Genjutsu' => 'hypnotic illusionary focus, subtle psychedelic chakra distortion in the background',
        'Fūinjutsu' => 'master of sealing jutsu with ancient inscribed talisman tags and ink scrolls',
        'Bukijutsu' => 'armed with dual kunai and shuriken weapon holsters',
        'Hiden' => 'master of secret clan techniques (Hiden Ninjutsu)',
    ];

    /**
     * Build the primary prompt for Meta Muse / Anime model.
     * Optimised specifically for Image-to-Image (Img2Img) style transfer:
     * Transforms the subject in the source photo into a 2D Naruto anime shinobi while
     * strictly preserving their facial identity, facial structure, skin tone and silhouette.
     */
    public function buildPrompt(Fighter $fighter): string
    {
        $clan = $fighter->clan;
        $clanCues = self::CLAN_VISUALS[$clan] ?? 'traditional shinobi clan garb with distinctive clan crest';

        $village = $fighter->village;
        $villageCues = self::VILLAGE_VISUALS[$village] ?? "{$village} Village metallic engraved shinobi forehead protector headband";

        $elem1 = $fighter->primary_element;
        $elemAura = self::ELEMENT_VISUALS[$elem1] ?? 'vibrant glowing chakra energy';
        if ($fighter->secondary_element && isset(self::ELEMENT_VISUALS[$fighter->secondary_element])) {
            $elemAura .= ', intertwined with '.self::ELEMENT_VISUALS[$fighter->secondary_element];
        }

        $rank = $fighter->ninja_rank;
        $spec = $fighter->specialization;
        $specVisual = self::SPEC_VISUALS[$spec] ?? "combat specialization in {$spec}";

        return implode(', ', [
            'Image-to-Image character adaptation of the person in the input photo',
            "turn the exact subject into an authentic {$rank} shinobi named {$fighter->name}",
            'retain and preserve the person\'s exact facial features, face shape, gaze, expression, skin tone and core identity',
            'stylize into hand-drawn 2D anime character in authentic Masashi Kishimoto Studio Pierrot Naruto Shippuden style',
            "wearing {$villageCues}",
            "attired as a prestigious member of the {$clan} clan ({$clanCues})",
            $specVisual,
            $elemAura,
            'crisp clean cel-shading, sharp ink lineart, vibrant anime colors, cinematic rim lighting',
            'official Databook key visual illustration, official Shonen Jump character art, 8k resolution, masterpiece',
        ]);
    }

    /**
     * Negative prompt to keep the output faithful to authentic 2D anime.
     */
    public function buildNegativePrompt(): string
    {
        return implode(', ', [
            'different person',
            'changed facial identity',
            'altered gender',
            'distorted face',
            '3d render',
            'photorealistic',
            'western comic style',
            'blurry',
            'lowres',
            'bad anatomy',
            'deformed eyes',
            'extra fingers',
            'missing limbs',
            'mutated hands',
            'ugly',
            'cropped',
            'watermark',
            'text',
            'signature',
            'jpeg artifacts',
        ]);
    }
}
