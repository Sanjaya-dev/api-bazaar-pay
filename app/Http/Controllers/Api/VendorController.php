<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::with('user')->get();
        return response()->json($vendors, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Hanya admin yang bisa membuat vendor
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'location' => 'required|string',
            'contact_number' => 'required|string',
            'business_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buat user vendor
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'vendor',
            'status' => 'active',
        ]);

        // Buat vendor terkait
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'location' => $request->location,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'business_type' => $request->business_type,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Vendor created successfully', 'vendor' => $vendor], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vendor = Vendor::with('user')->find($id);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        return response()->json($vendor, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== 'vendor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vendor->update($request->only(['company_name', 'location', 'contact_number', 'email', 'business_type', 'status']));

        return response()->json(['message' => 'Vendor updated successfully', 'vendor' => $vendor], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vendor->delete();
        return response()->json(['message' => 'Vendor deleted successfully'], 200);
    }
}
