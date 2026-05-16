<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    private function respond(Request $request, Order $order, string $message, string $event, int $status = 200, $comment = null)
    {
        if ($request->expectsJson()) {
            $payload = [
                'success' => true,
                'message' => $message,
            ];

            if ($comment) {
                $payload['comment'] = $comment->load('user');
            }

            return response()->json($payload, $status);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with($event, $message);
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, Order $order)
    {
        // Verify that user owns the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify order is delivered
        if ($order->status !== 'delivered') {
            return response()->json(['error' => 'Komentar hanya bisa ditambahkan untuk pesanan yang sudah diterima'], 400);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:3|max:1000',
        ]);

        $existingComment = $order->comments()
            ->where('user_id', Auth::id())
            ->first();

        if ($existingComment) {
            $existingComment->update([
                'rating' => $validated['rating'],
                'content' => $validated['content'],
            ]);

            return $this->respond($request, $order, 'Komentar berhasil diperbarui', 'success', 200, $existingComment);
        }

        $comment = $order->comments()->create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        return $this->respond($request, $order, 'Komentar berhasil ditambahkan', 'success', 201, $comment);
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, Order $order, Comment $comment)
    {
        // Verify that user owns the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify that user owns the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify comment belongs to the order
        if ($comment->order_id !== $order->id) {
            return response()->json(['error' => 'Komentar tidak termasuk dalam pesanan ini'], 400);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:3|max:1000',
        ]);

        $comment->update([
            'rating' => $validated['rating'],
            'content' => $validated['content'],
        ]);

        return $this->respond($request, $order, 'Komentar berhasil diperbarui', 'success', 200, $comment);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Request $request, Order $order, Comment $comment)
    {
        // Verify that user owns the order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify that user owns the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify comment belongs to the order
        if ($comment->order_id !== $order->id) {
            return response()->json(['error' => 'Komentar tidak termasuk dalam pesanan ini'], 400);
        }

        $comment->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus',
            ]);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Komentar berhasil dihapus');
    }
}
