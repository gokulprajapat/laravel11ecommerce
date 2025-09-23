<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
    }

    public function brands(){
        $brands=Brand::orderBy('id','desc')->paginate(10);
        return view('admin.brands',compact('brands'));
    }

    public function add_brand(){
        return view('admin.brand-add');
    }

    public function brand_store(Request $request){
        $request->validate([
            "name"=>"required",
            "slug"=>"required|unique:brands,slug",
            "image"=>"mimes:png,jpg,jpeg|max:5120"
        ]);

        $brand=new Brand();
        $brand->name=$request->name;
        $brand->slug=Str::slug($request->slug);
        $image=$request->file('image');
        $file_extension=$request->file('image')->extension();
        $file_name=Carbon::now()->timestamp.".".$file_extension;
        $this->GenerateBrandThumbnailImage($image,$file_name);
        $brand->image=$file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with("status","Brand has been added successfully!");
    }

    public function brand_edit($id){
        $brand=Brand::find($id);
        return  view("admin.brand-edit",compact('brand'));
    }

    public function brand_update(Request $request){
        $request->validate([
            "name"=>"required",
            "slug"=>"required|unique:brands,slug,".$request->id,
            "image"=>"mimes:jpg,jpeg,png|max:5120"
        ]);

        $brand=Brand::find($request->id);
        $brand->name=$request->name;
        $brand->slug=Str::slug($request->slug);
        if($request->hasFile('image')){
            if(File::exists(public_path("uploads/brands")."/".$brand->image)){
                File::delete(public_path("uploads/brands")."/".$brand->image);
            }
            $image=$request->file('image');
            $file_extension=$request->file('image')->extension();
            $file_name=Carbon::now()->timestamp.".".$file_extension;
            $this->GenerateBrandThumbnailImage($image,$file_name);
            $brand->image=$file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with("status","Brand has been updated successfully!");
    }

    public function GenerateBrandThumbnailImage($image,$imagename){
        $destinationPath=public_path("uploads/brands");
        $img=Image::read($image->path());
        $img->cover(124,124,'top');
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imagename);
    }

    public function brand_delete($id){
        $brand=Brand::find($id);
        if(File::exists((public_path("uploads/brands")."/".$brand->image))){
                File::delete((public_path("uploads/brands")."/".$brand->image));
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with("status","Brand has been deleted successfully");
    }

    public function categories(){
        $categories=Category::orderBy('id','desc')->paginate(10);
        return view('admin.categories',compact('categories'));
    }

    public function add_categories(){
        return view('admin.categories-add');
    }

    public function categories_store(Request $request){
        $request->validate([
            "name"=>"required",
            "slug"=>"required|unique:brands,slug",
            "image"=>"mimes:png,jpg,jpeg|max:5120"
        ]);

        $category=new Category();
        $category->name=$request->name;
        $category->slug=Str::slug($request->slug);
        $image=$request->file('image');
        $file_extension=$request->file('image')->extension();
        $file_name=Carbon::now()->timestamp.".".$file_extension;
        $this->GenerateCategoryThumbnailImage($image,$file_name);
        $category->image=$file_name;
        $category->save();
        return redirect()->route('admin.categories')->with("status","Category has been added successfully!");
    }

    public function GenerateCategoryThumbnailImage($image,$imagename){
        $destinationPath=public_path("uploads/categories");
        $img=Image::read($image->path());
        $img->cover(124,124,'top');
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imagename);
    }

    public function categories_edit($id){
        $category=Category::find($id);
        return  view("admin.categories-edit",compact('category'));
    }

    public function categories_update(Request $request){
        $request->validate([
            "name"=>"required",
            "slug"=>"required|unique:categories,slug,".$request->id,
            "image"=>"mimes:jpg,jpeg,png|max:5120"
        ]);

        $category=Category::find($request->id);
        $category->name=$request->name;
        $category->slug=Str::slug($request->slug);
        if($request->hasFile('image')){
            if(File::exists(public_path("uploads/categories")."/".$category->image)){
                File::delete(public_path("uploads/categories")."/".$category->image);
            }
            $image=$request->file('image');
            $file_extension=$request->file('image')->extension();
            $file_name=Carbon::now()->timestamp.".".$file_extension;
            $this->GenerateCategoryThumbnailImage($image,$file_name);
            $category->image=$file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with("status","Category has been updated successfully!");
    }

    public function categories_delete($id){
        $category=Category::find($id);
        if(File::exists((public_path("uploads/categories")."/".$category->image))){
                File::delete((public_path("uploads/categories")."/".$category->image));
        }
        $category->delete();
        return redirect()->route('admin.categories')->with("status","Category has been deleted successfully");
    }

    public function products(){
        // $products=Product::orderBy('created_at','DESC')->paginate(10);
        $products = Product::with(['category', 'brand'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function product_add(){
        $categories=Category::select('id','name')->orderBy('name')->get();
        $brands=Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',compact('categories','brands'));
    }

    public function product_store(Request $request){
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048',
            'category_id'=>'required',
            'brand_id'=>'required'
        ]);

        $product=new Product();
        $product->name=$request->name;
        $product->slug=Str::slug($request->name);
        $product->short_description=$request->short_description;
        $product->description=$request->description;
        $product->regular_price=$request->regular_price;
        $product->sale_price=$request->sale_price;
        $product->SKU=$request->SKU;
        $product->stock_status=$request->stock_status;
        $product->featured=$request->featured;
        $product->quantity=$request->quantity;
        $product->category_id=$request->category_id;
        $product->brand_id=$request->brand_id;

        $current_timestamp=Carbon::now()->timestamp;

        if($request->hasFile('image')){
            $image=$request->file('image');
            $imageName=$current_timestamp.".".$image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image=$imageName;
        }

        $gallery_arr= array();
        $gallery_images="";
        $counter=1;

        if($request->hasFile('images')){
            $allowedFileExtension=['jpg','png','jpeg'];
            $files=$request->file('images');
            $cnt=1;
            foreach($files as $file){
                $gextension=$file->getClientOriginalExtension();
                $gcheck=in_array($gextension,$allowedFileExtension);
                $current_timestamp=Carbon::now()->timestamp;
                if($gcheck){
                    $gFileName=$current_timestamp.'-'.$cnt.".".$gextension;
                    $this->GenerateProductThumbnailImage($file,$gFileName);
                    array_push($gallery_arr,$gFileName);
                    $counter=$counter+1;
                }
                $cnt=$cnt + 1;
            }
            $gallery_images=implode(",",$gallery_arr);
        }
        $product->images=$gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with("status","Product has been added successfully");
    }

    public function GenerateProductThumbnailImage($image,$imagename){
        $destinationPathThumbnail=public_path("uploads/products/thumbnails");
        $destinationPath=public_path("uploads/products");
        $img=Image::read($image->path());
        $img->cover(540,689,'top');
        $img->resize(540,689,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imagename);

        $img->cover(104,104,'top');
        $img->resize(104,104,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail.'/'.$imagename);
    }

    public function product_edit($id)
    {
        $product=Product::find($id);
          if (!$product) {
                return redirect()->back()->with('error', 'Product not found.');
            }
        $categories=Category::select('id','name')->orderBy('name')->get();
        $brands=Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',compact('product','categories','brands'));
    }

    public function deleteProductGalleryImage($productId, $image)
    {
        $product = Product::findOrFail($productId);
        $images = $product->images ? explode(',', $product->images) : [];
        $image = urldecode(trim($image));

        // Remove image from array
        $images = array_filter($images, function($img) use ($image) {
            return trim($img) !== $image;
        });

        // Delete file from storage
        $imagePath = public_path('uploads/products/' . $image);
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }

        // Update product images
        $product->images = implode(',', $images);
        $product->save();

        // return back()->with('status', 'Gallery image deleted.');

        return redirect()->route('admin.product.edit', ['id' => $productId])
            ->with('status', 'Gallery image deleted.');
    }

    public function product_update(){
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048',
            'category_id'=>'required',
            'brand_id'=>'required'
        ]);

        $product=new Product();
        $product->name=$request->name;
        $product->slug=Str::slug($request->name);
        $product->short_description=$request->short_description;
        $product->description=$request->description;
        $product->regular_price=$request->regular_price;
        $product->sale_price=$request->sale_price;
        $product->SKU=$request->SKU;
        $product->stock_status=$request->stock_status;
        $product->featured=$request->featured;
        $product->quantity=$request->quantity;
        $product->category_id=$request->category_id;
        $product->brand_id=$request->brand_id;

        $current_timestamp=Carbon::now()->timestamp;

        if($request->hasFile('image')){
            $image=$request->file('image');
            $imageName=$current_timestamp.".".$image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image=$imageName;
        }

        $gallery_arr= array();
        $gallery_images="";
        $counter=1;

        if($request->hasFile('images')){
            $allowedFileExtension=['jpg','png','jpeg'];
            $files=$request->file('images');
            $cnt=1;
            foreach($files as $file){
                $gextension=$file->getClientOriginalExtension();
                $gcheck=in_array($gextension,$allowedFileExtension);
                $current_timestamp=Carbon::now()->timestamp;
                if($gcheck){
                    $gFileName=$current_timestamp.'-'.$cnt.".".$gextension;
                    $this->GenerateProductThumbnailImage($file,$gFileName);
                    array_push($gallery_arr,$gFileName);
                    $counter=$counter+1;
                }
                $cnt=$cnt + 1;
            }
            $gallery_images=implode(",",$gallery_arr);
        }
        $product->images=$gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with("status","Product has been added successfully");
    }
}
