<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\File;
use App\Models\Product;

class ProductController extends Controller
{
    //method ini akan menampilkan halaman produk
    public function index(){
        $products = Product::orderBy('id', 'asc')->get();

        return view('products.list',[
            'products' => $products
        ]);
    }

    //method ini akan menampilkan halaman penambahan produk
    public function create(){
        return view('products.create');
    }

    //method ini akan menampilkan halaman pembelanjaan produk di dalam database
    public function store(Request $request){
        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric',
        ];

        if ($request->image != ""){
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()){
            return redirect()->route('products.create')->withInput()->withErrors($validator);
        }

        //disini kita akan insert produk di db
        $product = new Product();
        $product->id = $request->id;
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if ($request->image != ""){

            //kita akan membuat store image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext; //uniqeu image name

            //save image to products directory
            $image->move(public_path('uploads/products'),$imageName);

            //save image name in database
            $product->image = $imageName;
            $product->save();

        }

        return redirect()->route('products.index')->with('success','Product added successfully.');

    }

    //method ini akan menampilkan halaman pengeditan produk
    public function edit($id){
        $product = Product::findOrFail($id);
        return view('products.edit',[
            'product' => $product
        ]);
    }

    //method ini akan menampilkan pengupdatean produk
    public function update($id, Request $request){

        $product = Product::findOrFail($id);

        $rules = [
            'id' => 'required|numeric',
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric',
        ];

        if ($request->image != ""){
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()){
            return redirect()->route('products.edit', $product->id)->withInput()->withErrors($validator);
        }

        //disini kita akan insert produk
        $product->id = $request->id;
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if ($request->image != ""){

            //hapus foto lama
            File::delete(public_path('uploads/products/'.$product->image));

            //kita akan membuat store image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext; //uniqeu image name

            //save image to products directory
            $image->move(public_path('uploads/products'),$imageName);

            //save image name in database
            $product->image = $imageName;
            $product->save();

        }

        return redirect()->route('products.index')->with('success','Product updated successfully.');

    }

    //method ini digunakan untuk menghapus produk 
    public function destroy($id){
        $product = Product::findOrFail($id);

        //hapus foto
        File::delete(public_path('uploads/products/'.$product->image));

        //hapus produk dari database
        $product->delete();

        return redirect()->route('products.index')->with('success','Product deleted successfully.');

    }
}
