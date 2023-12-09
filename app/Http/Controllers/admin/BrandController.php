<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Validator;

class BrandController extends Controller
{

    public function index(Request $request){
    $brands = Brand::latest("id");

    if (!empty($request->get("keyword"))) {
        $brands = $brands->where("size", "like", "%" . $request->get("keyword") . "%");
    }

    $brands = $brands->paginate(10);

    return view("admin/brands/list", compact("brands"));
}
    public function create()
    {
        return view("admin/brands/create");
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "size" => "required",
            "slug" => "required|unique:brands",
            'status' => 'required',
        ]);
        if ($validator->passes()) {
            $brand = new Brand();
            $brand->size = $request->size;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success', 'Brand added successfully.');


            return response()->json([
                "status" => true,
                "message" => "Brand addedd successfully",
            ]);
        } else {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors()
            ]);
        }

    }
    public function edit($id, Request $request)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            $request->session()->flash("error", "Record not found.");
            return redirect()->route("brands.index");
        }
        $data["brand"] = $brand;
        return view("admin/brands/edit", $data);

    }

    public function update(Request $request, $id)
    {

        $brand = Brand::find($id);

        if (empty($brand)) {
            $request->session()->flash("error", "Record not found.");
            return response()->json([
                "status" => false,
                "notFound" => true,
                "message" => "Brand not found",
            ]);
            // return redirect()->route("brands.index");
        }

        $validator = Validator::make($request->all(), [
            "size" => "required",
            "slug" => "required|unique:brands,slug," . $brand->id . ",id",
            'status' => 'required',
        ]);
        if ($validator->passes()) {
            $brand->size = $request->size;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success', 'Brand updated successfully.');

            return response()->json([
                "status" => true,
                "message" => "Brand updated successfully",
            ]);
        } else {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors()
            ]);
        }
    }

    public function destroy ($id, Request $request) {

        $brand = Brand::find($id);

        if (empty($brand)){
            $request->session()->flash('error','Record not found');
            return response([
            "status" => false,
            "notFound" => true
            ]);
          
        }
        $brand->delete();

        $request->session()->flash('success', 'Brand deleted successfully.');

        return response([
            'status' => true,
            'message' => 'Brand deleted successfully.'
        ]);
    }

}