<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PassRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PassRequestAdminController extends Controller
{
    /**
     * Display a listing of pass verification requests.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = PassRequest::with(['user.fighter', 'processor'])
            ->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $counts = [
            'pending' => PassRequest::where('status', 'pending')->count(),
            'approved' => PassRequest::where('status', 'approved')->count(),
            'rejected' => PassRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.pass_requests.index', compact('requests', 'status', 'counts'));
    }

    /**
     * Approve a pass verification request and activate user pass.
     */
    public function approve(Request $request, PassRequest $passRequest): RedirectResponse
    {
        $user = $passRequest->user;

        // Activate pass for user
        $user->update([
            'has_paid_microtransaction' => true,
        ]);

        // Trigger portrait generation if shinobi exists
        if ($user->fighter) {
            $user->fighter->update([
                'portrait_status' => 'pending',
                'portrait_ordered_at' => now(),
            ]);
        }

        // Update pass request
        $passRequest->update([
            'status' => 'approved',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'user_notified' => false,
        ]);

        return back()->with('success', "Le Pass Shinobi de {$user->username} a été activé avec succès ! Une notification lui sera affichée.");
    }

    /**
     * Reject a pass verification request with an explanation reason.
     */
    public function reject(Request $request, PassRequest $passRequest): RedirectResponse
    {
        $validated = $request->validate([
            'admin_rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'admin_rejection_reason.required' => 'Veuillez indiquer le motif du refus afin d\'en informer le joueur.',
        ]);

        $passRequest->update([
            'status' => 'rejected',
            'admin_rejection_reason' => $validated['admin_rejection_reason'],
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'user_notified' => false,
        ]);

        return back()->with('info', "La demande de {$passRequest->user->username} a été rejetée. Le motif lui sera transmis dans sa modale de notification.");
    }

    /**
     * Delete/archive a processed pass request.
     */
    public function destroy(PassRequest $passRequest): RedirectResponse
    {
        $passRequest->delete();

        return back()->with('success', 'La demande a été définitivement supprimée des archives.');
    }
}
