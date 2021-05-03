<?php

namespace App\Http\Controllers\User\Admin\Item;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Admin\ServiceController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    protected $service;

    public function __construct(ServiceController $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::with('products')->get();
        $response = $this->service->index($categories);
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];

        $rules = $this->validation($request->all());
        try {
            if($rules->fails()){
                $response["message"] = $rules->errors();
                $response["error"] = true;
            }else{
                $data = Category::create($request->all());
                $response["message"] = "Successfully Added ".$data->name." in Category!";
                $response["data"] = $data;
                $response["error"] = false;
            }
        } catch (\Exception $error) {
            $response["message"] = "Error ".$error->getMessage();
            $response["error"] = true;
        }
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeProduct(Request $request, Category $category)
    {
        $response = [];
        $rules = $this->productValidation($request->all());
        try {
            if($rules->fails()){
                $response["message"] = $rules->errors();
                $response["error"] = true;
            }else{
                $product = $request->all();
                $product["avail_unit_measure"] = $product["unit_measure"];
                $data = $category->products()->create($product);
                $response["message"] = "Successfully Added ".$data->name." in Product!";
                $response["data"] = $data;
                $response["error"] = false;
            }
        } catch (\Exception $error) {
            $response["message"] = "Error ".$error->getMessage();
            $response["error"] = true;
        }
        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $response = $this->service->show($category);
        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $response = [];

        $rules = $this->validation($request->all());
        try {
            if($rules->fails()){
                $response["message"] = $rules->errors();
                $response["error"] = true;
            }else{
                $category->update($request->all());
                $response["message"] = "Successfully Updated ".$category->name;
                $response["data"] = $category;
                $response["error"] = false;
            }
        } catch (\Exception $error) {
            $response["message"] = "Error ".$error->getMessage();
            $response["error"] = true;
        }
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $response = $this->service->destroy($category);

        return response()->json($response);
    }

    /**
     * Validate the request provided.
     *
     * @param  int  $data
     * @return \Illuminate\Http\Response
     */
    public function validation($data)
    {
        $rules = Validator::make($data,[
            'name' => 'required|regex:/^[\pL\s\-]+$/u',
        ]);
        return $rules;
    }

    public function productValidation($data)
    {
        $rules = Validator::make($data,[
            'name' => 'required|regex:/^[\pL\s\-]+$/u',
            'unit_measure' => 'required|numeric',
            'price' => 'required|numeric',
            'part' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg'
        ],[
            'part.required' => 'This field is required, please enter valid value'
        ]);
        return $rules;
    }
}
