<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            // Log the user in
            auth()->login($user);

            // Migrate cart items from session to user
            $this->migrateSessionCartToUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'user' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'postal_code' => 'required|string|max:10',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'password' => Hash::make($validated['password']),
            ]);

            // Dispatch Registered event to trigger email verification
            event(new Registered($user));

            // Log the user in
            auth()->login($user);

            // Migrate cart items from session to user
            $this->migrateSessionCartToUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Register berhasil',
                'user' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If it's an AJAX request, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);
        }

        // Otherwise redirect to home
        return redirect('/');
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser()
    {
        if (auth()->check()) {
            return response()->json([
                'authenticated' => true,
                'success' => true,
                'user' => auth()->user()
            ]);
        }

        return response()->json([
            'authenticated' => false,
            'success' => false,
            'user' => null
        ]);
    }

    /**
     * Migrate cart items from session to user
     */
    private function migrateSessionCartToUser(User $user)
    {
        $sessionId = session()->getId();

        // Get existing cart items for this session
        $sessionCartItems = \App\Models\CartItem::where('session_id', $sessionId)
            ->where('user_id', null)
            ->get();

        foreach ($sessionCartItems as $item) {
            // Check if user already has this product in cart
            $existingItem = \App\Models\CartItem::where('user_id', $user->id)
                ->where('product_id', $item->product_id)
                ->first();

            if ($existingItem) {
                // Update quantity if product already in user's cart
                $existingItem->increment('quantity', $item->quantity);
                $item->delete();
            } else {
                // Assign item to user
                $item->update([
                    'user_id' => $user->id,
                    'session_id' => null
                ]);
            }
        }
    }
}
