<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fighter;
use App\Services\ImageGeneration\ShinobiPromptBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortraitAdminController extends Controller
{
    public function __construct(
        protected ShinobiPromptBuilder $promptBuilder,
    ) {}

    /**
     * Display the list of pending / completed shinobi portraits.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $fightersQuery = Fighter::query()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('has_paid_microtransaction', true));

        if ($status === 'pending') {
            $fightersQuery->where('portrait_status', 'pending');
        } elseif ($status === 'ready') {
            $fightersQuery->where('portrait_status', 'ready');
        }

        $fighters = $fightersQuery->latest('portrait_ordered_at')->paginate(15);

        // Pre-build dynamic prompts for each fighter to display in UI
        $fighters->getCollection()->transform(function ($fighter) {
            $fighter->generated_prompt = $this->promptBuilder->buildPrompt($fighter);
            $fighter->negative_prompt = $this->promptBuilder->buildNegativePrompt();

            return $fighter;
        });

        $counts = [
            'pending' => Fighter::where('portrait_status', 'pending')->count(),
            'ready' => Fighter::where('portrait_status', 'ready')->count(),
            'total_paid' => Fighter::whereHas('user', fn ($q) => $q->where('has_paid_microtransaction', true))->count(),
        ];

        return view('admin.portraits.index', [
            'fighters' => $fighters,
            'counts' => $counts,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Download the fighter's source photo directly.
     */
    public function downloadSourcePhoto(Fighter $fighter)
    {
        $path = $fighter->source_photo_path;

        if (! $path) {
            abort(404, 'Aucune photo source enregistrée pour ce combattant.');
        }

        // Si le chemin commence par /storage/
        $cleanPath = preg_replace('#^/storage/#', '', $path);
        if (Storage::disk('public')->exists($cleanPath)) {
            return Storage::disk('public')->download($cleanPath, "photo_source_shinobi_{$fighter->id}.jpg");
        }

        // Si c'est un fichier local direct
        if (file_exists(public_path($path))) {
            return response()->download(public_path($path), "photo_source_shinobi_{$fighter->id}.jpg");
        }

        return redirect($fighter->source_photo_url);
    }

    /**
     * Upload and publish the generated portrait for a fighter.
     */
    public function upload(Request $request, Fighter $fighter): RedirectResponse
    {
        $request->validate([
            'portrait_file' => ['required', 'image', 'max:10240'], // Max 10MB
        ]);

        $file = $request->file('portrait_file');
        $filename = 'portraits/shinobi_'.$fighter->id.'_'.Str::random(10).'.'.$file->getClientOriginalExtension();

        Storage::disk('public')->put($filename, file_get_contents($file));
        $publicUrl = Storage::url($filename);

        $fighter->update([
            'portrait_path' => $publicUrl,
            'portrait_status' => 'ready',
        ]);

        return back()->with('success', "Le portrait officiel du Shinobi #{$fighter->id} ({$fighter->name}) a été publié avec succès ! Le joueur peut le contempler sur sa carte.");
    }
}
