<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\SubCategory;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null)
    {

        $categorySelected = '';
        $subCategorySelected = '';

        $brandsArray = [];

        $categories = Category::orderBy('name', 'ASC')->with('sub_category')->where('status', 1)->get();
        $brands = Brand::orderBy('size', 'ASC')->where('status', 1)->get();
        // $products = Product::orderBy('id','DESC')->where('status',1)->get();

        $products = Product::where('status', 1);

        //Filtering
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            $products = $products->where('category_id', $category->id);
            $categorySelected = $category->id;
        }

        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            $products = $products->where('sub_category_id', $subCategory->id);
            $subCategorySelected = $subCategory->id;
        }

        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            if ($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);

            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }
        if (!empty($request->get('search'))) {
            $products = $products->where('name', 'like', '%' . $request->get('search') . '%');

        }


        if ($request->get('sort') != '') {
            if ($request->get('sort') == 'latest') {
                $products = $products->orderBy('id', 'DESC');
            } else if ($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'ASC');
            } else {
                $products = $products->orderBy('price', 'DESC');
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }


        $products = $products->orderBy('id', 'DESC');
        $products = $products->paginate(6);

        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;

        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;

        $data['brandsArray'] = $brandsArray;

        $data['priceMax'] = (intval($request->get('price_max')) == 0) ? 1000 : $request->get('price_max');
        $data['priceMin'] = intval($request->get('price_min'));

        $data['sort'] = $request->get('sort');




        return view('front\shop', $data);
    }

    public function product($slug)
    {
        // echo $slug;
        $product = Product::where('slug', $slug)
            ->withCount('product_ratings')
            ->withSum('product_ratings', 'rating')
            ->with('product_images', 'product_ratings')->first();

        if ($product == null) {
            abort(404);
        }

        //Fetch Related Products
        $relatedProducts = [];
        if ($product->related_products != '') {
            $productArray = explode(',', $product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->where('status', 1)->with('product_images')->get();

        }

        $data['product'] = $product;
        $data['relatedProducts'] = $relatedProducts;

        // Rating Calculation

        $avgRating = '0.00';
        $avgRatingPer = 0;
        if ($product->product_ratings_count > 0) {
            $avgRating = number_format(($product->product_ratings_sum_rating / $product->product_ratings_count), 2);
            $avgRatingPer = ($avgRating * 100) / 5;
        }
        $data['avgRating'] = $avgRating;
        $data['avgRatingPer'] = $avgRatingPer;

        return view('front\product', $data);
    }

    // public function saveRating(Request $request, $id){
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'email' => 'required|email',
    //         // 'comment' => 'required',
    //         'rating' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors()
    //         ]);
    //     }

    //      // User not logged in
    //      if (!Auth::check()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'You need to be logged in to rate this product.',
    //             'redirect' => route('account.login')
    //         ]);
    //     }
    
    //     // Check if the logged-in user has already reviewed this product
    //     $user = Auth::user();
    //     $count = ProductRating::where('product_id', $id)
    //         ->where('email', $user->email)
    //         ->count();

    //     $count = ProductRating::where('email', $request->email)->count();
    //     if ($count > 0) {
    //         session()->flash('error', 'You already rated this product');
    //         return response()->json([
    //             'status' => true,

    //         ]);

    //     }

    //     $productRating = new ProductRating;
    //     $productRating->product_id = $id;
    //     $productRating->username = $request->name;
    //     $productRating->email = $request->email;
    //     $productRating->comment = $request->comment;
    //     $productRating->rating = $request->rating;
    //     $productRating->status = 0;
    //     $productRating->save();

    //     session()->flash('success', 'Thank you for your rating!');

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Thank you for your rating!'
    //     ]);
    // }

    public function saveRating(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email',
        'rating' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }

    // User not logged in
    if (!Auth::check()) {
        return response()->json([
            'status' => false,
            'message' => 'You need to be logged in to rate this product.',
            'redirect' => route('account.login')
        ]);
    }

    // Check if the logged-in user has already reviewed this product
    $user = Auth::user();
    $count = ProductRating::where('product_id', $id)
        ->where('email', $user->email)
        ->count();

    if ($count > 0) {
        session()->flash('error', 'You have already rated this product.');
        return response()->json([
            'status' => true,
            'message' => 'You have already rated this product.'
        ]);
    }

    $productRating = new ProductRating;
    $productRating->product_id = $id;
    $productRating->username = $request->name;
    $productRating->email = $user->email; // Use logged-in user's email
    $productRating->comment = $request->comment;
    $productRating->rating = $request->rating;
    $productRating->status = 1;
    $productRating->save();

    session()->flash('success', 'Thank you for your rating!');

    return response()->json([
        'status' => true,
        'message' => 'Thank you for your rating!'
    ]);
}

}


