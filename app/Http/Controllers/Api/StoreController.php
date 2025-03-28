<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Store;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function index()
    {
        // Jika admin, bisa melihat semua store
        if (Auth::user()->role === 'admin') {
            $stores = Store::with('vendor')->get();
        } else if (Auth::user()->role === 'vendor') {
            // Jika vendor, hanya bisa melihat store miliknya
            $vendor = Vendor::where('user_id', Auth::id())->first();
            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found'], 404);
            }
            $stores = Store::where('vendor_id', $vendor->id)->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($stores, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'location' => 'required|string',
            'vendor_id' => 'exists:vendors,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vendor = null;
        if (Auth::user()->role === 'vendor') {
            $vendor = Vendor::where('user_id', Auth::id())->first();
            if (!$vendor || $vendor->id != $request->vendor_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Buat user store
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'store',
            'status' => 'active',
        ]);

        $store = Store::create([
            'vendor_id' => $request->vendor_id ?? $vendor->id,
            'user_id' => $user->id,
            'name' => $request->name,
            'location' => $request->location,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Store created successfully', 'store' => $store], 201);
    }

    public function show($id)
    {
        $store = Store::with('vendor')->find($id);
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        if (Auth::user()->role === 'vendor') {
            $vendor = Vendor::where('user_id', Auth::id())->first();
            if (!$vendor || $store->vendor_id != $vendor->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($store, 200);
    }

    public function update(Request $request, $id)
    {
        $store = Store::find($id);
        $user = User::find($store->user_id);
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        if (Auth::user()->role === 'vendor') {
            $vendor = Vendor::where('user_id', Auth::id())->first();
            if (!$vendor || $store->vendor_id != $vendor->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $user->update($request->only(['name', 'email', 'password', 'status']));
        $store->update($request->only(['name', 'location', 'status']));

        return response()->json(['message' => 'Store updated successfully', 'store' => $store], 200);
    }

    public function destroy($id)
    {
        $store = Store::find($id);
        $user = User::find($store->user_id);
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        if (Auth::user()->role === 'vendor') {
            $vendor = Vendor::where('user_id', Auth::id())->first();
            if (!$vendor || $store->vendor_id != $vendor->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();
        $store->delete();
        return response()->json(['message' => 'Store deleted successfully'], 200);
    }
}
