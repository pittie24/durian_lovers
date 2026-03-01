<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmationController extends Controller
{
    /**
     * Display list of payment confirmations
     */
    public function index()
    {
        $confirmations = PaymentConfirmation::with(['order', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.payment-confirmations.index', [
            'confirmations' => $confirmations,
        ]);
    }

    /**
     * Show confirmation detail
     */
    public function show(PaymentConfirmation $confirmation)
    {
        $confirmation->load(['order.items.product', 'user', 'verifiedBy']);

        return view('admin.payment-confirmations.show', [
            'confirmation' => $confirmation,
        ]);
    }

    /**
     * Approve payment confirmation
     */
    public function approve(Request $request, PaymentConfirmation $confirmation)
    {
        $admin = Auth::user();

        $confirmation->approve($admin->id);

        return redirect()->route('admin.payment-confirmations.index')
            ->with('success', 'Konfirmasi pembayaran berhasil disetujui.');
    }

    /**
     * Reject payment confirmation
     */
    public function reject(Request $request, PaymentConfirmation $confirmation)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $admin = Auth::user();

        $confirmation->reject($admin->id, $request->notes);

        return redirect()->route('admin.payment-confirmations.index')
            ->with('success', 'Konfirmasi pembayaran ditolak.');
    }

    /**
     * Delete confirmation and proof image
     */
    public function destroy(PaymentConfirmation $confirmation)
    {
        // Delete proof image
        if ($confirmation->proof_image) {
            Storage::disk('public')->delete($confirmation->proof_image);
        }

        $confirmation->delete();

        return redirect()->route('admin.payment-confirmations.index')
            ->with('success', 'Konfirmasi pembayaran dihapus.');
    }
}
