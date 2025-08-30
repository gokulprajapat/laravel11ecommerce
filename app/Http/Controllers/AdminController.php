<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        return view('admin.add-brand');
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

    public function GenerateBrandThumbnailImage($image,$imagename){
        $destinationPath=public_path("uploads/brands");
        $img=Image::read($image->path);
        $img->cover(124,124,'top');
        $img->resize(124,124,function($constraint){
            $constraint->aspectRation();
        })->save($destinationPath.'/'.$imagename); 
    }
}
