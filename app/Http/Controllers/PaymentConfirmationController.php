<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmationController extends Controller
{
    /**
     * Show payment confirmation page
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $confirmation = $order->paymentConfirmation;

        return view('customer.payment-confirmation.show', [
            'order' => $order,
            'confirmation' => $confirmation,
        ]);
    }

    /**
     * Store payment confirmation
     */
    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if already confirmed
        if ($order->paymentConfirmation && $order->paymentConfirmation->isApproved()) {
            return redirect()->route('pembayaran.confirmation.show', $order)
                ->with('info', 'Pembayaran sudah diverifikasi sebelumnya.');
        }

        $data = $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'bank_name' => 'required|string|max:100',
            'account_name' => 'required|string|max:255',
            'transfer_amount' => 'required|numeric|min:0',
        ]);

        // Upload proof image
        if ($request->hasFile('proof_image')) {
            $image = $request->file('proof_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('payment-confirmations', $imageName, 'public');

            // Delete old confirmation if exists
            if ($order->paymentConfirmation) {
                if ($order->paymentConfirmation->proof_image) {
                    Storage::disk('public')->delete($order->paymentConfirmation->proof_image);
                }
                $order->paymentConfirmation->delete();
            }

            // Create new confirmation
            PaymentConfirmation::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'proof_image' => $imagePath,
                'bank_name' => $data['bank_name'],
                'account_name' => $data['account_name'],
                'transfer_amount' => $data['transfer_amount'],
                'status' => 'PENDING',
            ]);

            // Update order status
            $order->update([
                'status' => 'MENUNGGU_PEMBAYARAN',
            ]);
        }

        return redirect()->route('pembayaran.confirmation.show', $order)
            ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
    }

    /**
     * Resubmit confirmation if rejected
     */
    public function resubmit(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $confirmation = $order->paymentConfirmation;

        if (!$confirmation || !$confirmation->isRejected()) {
            return redirect()->route('pembayaran.confirmation.show', $order)
                ->with('error', 'Hanya konfirmasi yang ditolak yang bisa diajukan ulang.');
        }

        return view('customer.payment-confirmation.resubmit', [
            'order' => $order,
            'confirmation' => $confirmation,
        ]);
    }
}
