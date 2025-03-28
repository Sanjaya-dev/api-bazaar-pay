<?php

namespace App\Http\Controllers\API;

use App\Models\Store;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{


    public function index()
    {
        // Hanya admin bisa melihat semua produk, store owner hanya bisa melihat produknya
        if (Auth::user()->role === 'admin') {
            $products = Product::with('store')->get();
        } elseif (Auth::user()->role === 'vendor') {
            $vendor = Vendor::where('user_id', Auth::id())->first();
            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found'], 404);
            }
            // Ambil semua store milik vendor
            $stores = Store::where('vendor_id', $vendor->id)->pluck('id');
            // Ambil semua produk dari store yang dimiliki vendor
            $products = Product::whereIn('store_id', $stores)->get();
            // $products = Product::where('vendor_id', $vendor->id)->get();
            // $products = Product::whereHas('store', function ($query) {
            //     $query->where('stores.user_id', Auth::id());
            // })->get();
        }else {
            $products = Product::whereHas('store', function ($query) {
                $query->where('stores.id', Auth::id());
            })->get();
        }
        
        return response()->json($products, 200);
    }

    public function store(Request $request)
    {
        // if (!in_array(Auth::user()->role, ['admin', 'store'])) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // Cek apakah store_id milik store owner yang login
        $store = Store::find($request->store_id);
        if (Auth::user()->role !== 'admin' && $store->user_id !== Auth::id() && Auth::id() !== $store->vendor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:100',
            'description' => 'string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:instock,low_stock,out_of_stock',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'store_id' => $request->store_id,
            'name' => $request->name,
            'image' => $path,
            'category' => $request->category,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    public function show($id)
    {
        $product = Product::with('store')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Hanya admin atau pemilik store yang bisa melihat produk
        if (Auth::user()->role !== 'admin' && $product->store->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($product, 200);
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'store'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $store = Store::find($product->store_id);
        // Hanya admin atau pemilik store yang bisa mengupdate
        if (Auth::user()->role !== 'admin' && $store->user_id !== Auth::id() && Auth::id() !== $store->vendor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'string|max:100',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
            'status' => 'in:instock,low_stock,out_of_stock',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($product->image);
            // $filename = now()->format('YmdHi') . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->update($request->only(['name', 'category', 'description', 'price', 'stock', 'status']));

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    public function destroy($id)
    {
        // Cek apakah pengguna memiliki hak akses
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'store' && Auth::user()->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Cari produk berdasarkan ID
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Pastikan pengguna hanya bisa menghapus produk miliknya sendiri (jika bukan admin)
        if (Auth::user()->role === 'store' && $product->store->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Tambahkan logika untuk role vendor
        if (Auth::user()->role === 'vendor' && $product->store->vendor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Hapus gambar jika ada
        if (!empty($product->image) && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        // Hapus produk dari database
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
