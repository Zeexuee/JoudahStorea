<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class TestAutoUpdateController extends Controller
{
    /**
     * Test auto-update by manually setting payment status to completed
     */
    public function testPaymentAutoUpdate(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Authorize - admin only
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Only admin can use this endpoint');
        }

        $payment = $order->payment;
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Store old values
        $oldPaymentStatus = $payment->status;
        $oldOrderStatus = $order->status;

        // Update payment status to completed - This should trigger model observer
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Refresh to get new values
        $payment->refresh();
        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment updated to completed. Order status should auto-update.',
            'before' => [
                'payment_status' => $oldPaymentStatus,
                'order_status' => $oldOrderStatus,
            ],
            'after' => [
                'payment_status' => $payment->status,
                'order_status' => $order->status,
            ],
            'auto_updated' => $oldOrderStatus !== $order->status,
        ]);
    }

    /**
     * Test logs to see if auto-update triggered
     */
    public function testLogs()
    {
        // Authorize - admin only
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Only admin can use this endpoint');
        }

        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }

        // Get last 50 lines
        $logs = array_reverse(file($logFile));
        $recentAutoUpdates = [];

        foreach ($logs as $line) {
            if (strpos($line, 'Auto-update') !== false || strpos($line, 'payment_model_observer') !== false) {
                $recentAutoUpdates[] = trim($line);
                if (count($recentAutoUpdates) >= 20) break;
            }
        }

        return response()->json([
            'auto_update_logs' => array_reverse($recentAutoUpdates),
            'total_logs_found' => count($recentAutoUpdates),
        ]);
    }

    /**
     * Get order and payment status
     */
    public function checkStatus($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Authorize - admin only
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Only admin can use this endpoint');
        }

        $payment = $order->payment;

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_status' => $order->status,
            'payment' => $payment ? [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at,
            ] : null,
        ]);
    }
}
