<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use App\Models\MultiImg;
use App\Models\SubCategory;

class ProductController extends Controller
{
    public function AllProduct()
    {
        $products = Product::latest()->get();
        return view('backend.product.product_all', compact('products'));
    } // End Method

    public function AddProduct()
    {
        $activeVendor = User::where('status', 'active')
            ->where('role', 'vendor')
            ->latest()
            ->get();
        $brands = Brand::latest()->get();
        $categories = Category::latest()->get();
        return view('backend.product.product_add', compact('brands', 'categories', 'activeVendor'));
    } // End Method

    public function StoreProduct(Request $request)
    {
        $image = $request->file('product_thambnail');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)
            ->resize(800, 800)
            ->save('upload/products/thambnail/' . $name_gen);
        $save_url = 'upload/products/thambnail/' . $name_gen;

        // 1st side image
        $image = $request->file('first_image');
        $name_gen1 = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)
            ->resize(800, 800)
            ->save('upload/products/thambnail/' . $name_gen1);
        $save_url1 = 'upload/products/thambnail/' . $name_gen1;

        //feature Image
        $image = $request->file('feature_image');
        $name_gen2 = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)
            ->resize(800, 800)
            ->save('upload/products/thambnail/' . $name_gen2);
        $save_url_feature_image = 'upload/products/thambnail/' . $name_gen2;

        $project_id = Product::insertGetId([
            'project_name' => $request->project_name,
            'project_slug' => strtolower(str_replace(' ', '-', $request->project_name)),
            'project_address' => $request->project_address,
            'project_category' => $request->project_category,
            'project_status' => $request->project_status,
            'project_video' => $request->project_video,
            'project_glance' => $request->project_glance,
            'features' => $request->features,
            'first_image' => $save_url1,
            'feature_image' => $save_url_feature_image,
            'product_thambnail' => $save_url,
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);

        /// Multiple Image Upload From her //////

        $images = $request->file('gallery_img');
        foreach ($images as $img) {
            $make_name = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension();
            Image::make($img)
                ->resize(800, 800)
                ->save('upload/products/multi-image/' . $make_name);
            $uploadPath = 'upload/products/multi-image/' . $make_name;

            MultiImg::insert([
                'project_id' => $project_id,
                'photo_name' => $uploadPath,
                'created_at' => Carbon::now(),
            ]);
        } // end foreach
        /// End Multiple Image Upload From her //////

        $notification = [
            'message' => 'Product Inserted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->route('all.product')
            ->with($notification);
    } // End Method

    public function EditProduct($id)
    {
        $multiImgs = MultiImg::where('project_id', $id)->get();
        $activeVendor = User::where('status', 'active')
            ->where('role', 'vendor')
            ->latest()
            ->get();
        $brands = Brand::latest()->get();
        $categories = Category::latest()->get();
        $subcategory = SubCategory::latest()->get();
        $products = Product::findOrFail($id);
        return view('backend.product.product_edit', compact('brands', 'categories', 'activeVendor', 'products', 'subcategory', 'multiImgs'));
    } // End Method

    public function UpdateProduct(Request $request)
    {
        $product_id = $request->id;

        Product::findOrFail($product_id)->update([
            'project_name' => $request->project_name,
            'project_slug' => strtolower(str_replace(' ', '-', $request->project_name)),
            'project_address' => $request->project_address,
            'project_category' => $request->project_category,
            'project_status' => $request->project_status,
            'project_video' => $request->project_video,
            'project_glance' => $request->project_glance,
            'features' => $request->features,
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);

        $notification = [
            'message' => 'Product Updated Without Image Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->route('all.product')
            ->with($notification);
    } // End Method

    public function UpdateProductThambnail(Request $request)
    {
        $pro_id = $request->id;
        $oldImage = $request->old_img;

        $image = $request->file('product_thambnail');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)
            ->resize(800, 800)
            ->save('upload/products/thambnail/' . $name_gen);
        $save_url = 'upload/products/thambnail/' . $name_gen;

        if (file_exists($oldImage)) {
            unlink($oldImage);
        }

        Product::findOrFail($pro_id)->update([
            'product_thambnail' => $save_url,
            'updated_at' => Carbon::now(),
        ]);

        $notification = [
            'message' => 'Product Image Thambnail Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function UpdateGlanceImage(Request $request)
    {
        $pro_id = $request->id;
        $oldImage = $request->old_img;

        $image = $request->file('first_image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)
            ->resize(800, 800)
            ->save('upload/products/thambnail/' . $name_gen);
        $save_url = 'upload/products/thambnail/' . $name_gen;

        if (file_exists($oldImage)) {
            unlink($oldImage);
        }

        Product::findOrFail($pro_id)->update([
            'first_image' => $save_url,
            'updated_at' => Carbon::now(),
        ]);

        $notification = [
            'message' => 'Product Image Thambnail Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function UpdateFeatureImage(Request $request)
    {
        $pro_id = $request->id;
        $oldImage = $request->old_img;

        $image = $request->file('feature_image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)
            ->resize(800, 800)
            ->save('upload/products/thambnail/' . $name_gen);
        $save_url = 'upload/products/thambnail/' . $name_gen;

        if (file_exists($oldImage)) {
            unlink($oldImage);
        }

        Product::findOrFail($pro_id)->update([
            'feature_image' => $save_url,
            'updated_at' => Carbon::now(),
        ]);

        $notification = [
            'message' => 'Product Image Thambnail Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    // Multi Image Update
    public function UpdateProductMultiimage(Request $request)
    {
        $imgs = $request->multi_img;

        foreach ($imgs as $id => $img) {
            $imgDel = MultiImg::findOrFail($id);
            unlink($imgDel->photo_name);

            $make_name = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension();
            Image::make($img)
                ->resize(800, 800)
                ->save('upload/products/multi-image/' . $make_name);
            $uploadPath = 'upload/products/multi-image/' . $make_name;

            MultiImg::where('id', $id)->update([
                'photo_name' => $uploadPath,
                'updated_at' => Carbon::now(),
            ]);
        } // end foreach

        $notification = [
            'message' => 'Product Multi Image Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function MulitImageDelelte($id)
    {
        $oldImg = MultiImg::findOrFail($id);
        unlink($oldImg->photo_name);

        MultiImg::findOrFail($id)->delete();

        $notification = [
            'message' => 'Product Multi Image Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function ProductInactive($id)
    {
        Product::findOrFail($id)->update(['status' => 0]);
        $notification = [
            'message' => 'Product Inactive',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function ProductActive($id)
    {
        Product::findOrFail($id)->update(['status' => 1]);
        $notification = [
            'message' => 'Product Active',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function ProductDelete($id)
    {
        $product = Product::findOrFail($id);
        unlink($product->product_thambnail);
        Product::findOrFail($id)->delete();

        MultiImg::where('product_id', $id)->delete();
        foreach ($imges as $img) {
            unlink($img->photo_name);
            MultiImg::where('product_id', $id)->delete();
        }

        $notification = [
            'message' => 'Product Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()
            ->back()
            ->with($notification);
    } // End Method

    public function ProductStock()
    {
        $products = Product::latest()->get();
        return view('backend.product.product_stock', compact('products'));
    } // End Method
}
