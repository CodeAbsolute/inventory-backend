<?php

namespace App\Http\Controllers;


use App\Models\Image;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductResourceController extends Controller
{
    public $path = "products/";

    /** Delete images in products folder.
     * @param  integer $product_id => product id
     * @param  array $onlyDeleteSelectedImagesArray => array of image ids to be deleted
     * @return array
     */
    protected function deleteImages($product_id, $onlyDeleteSelectedImagesArray = [])
    {
        try {
            // var_dump($onlyDeleteSelectedImagesArray);
            // deleting selected images
            if (count($onlyDeleteSelectedImagesArray) > 0) {
                foreach ($onlyDeleteSelectedImagesArray as $imageId) {
                    $image = Image::where(['id' => $imageId, 'product_id' => $product_id])->first();
                    // echo $image;
                    $image->delete();
                }
            } else {
                // getting images from db
                $images = Image::where('product_id', $product_id)->get();
                if (count($images) == 0) {
                    return ['message' => 'images not found'];
                }
                // var_dump($images);
                // deleting all images
                foreach ($images as $image) {
                    // unlink($image);
                    $image->delete();
                }
            }
            return [];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    /** Save images in products folder.
     * @param  \Illuminate\Http\Request  $request
     * @param  integer $product_id
     * @param  string $folderPath
     * @return array
     */
    protected function saveImages(Request $request, $product_id, $folderPath)
    {
        try {
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    //  store images in products folder
                    $filename = time() . '_' . $image->getClientOriginalName();
                    // $filename = time() . '.' . $image->getClientOriginalName();
                    $image->move($folderPath, $filename);
                    // saving image path and product id in images table
                    $newImage = new Image();
                    $newImage->product_id = $product_id;
                    $newImage->path = $folderPath . '/' . $filename;
                    $newImage->created_at = Carbon::now();
                    $newImage->save();
                    // echo $newImage;
                }
            }
            return [];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /* Validate the product data.
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function validateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | min:10 | max:100',
            'in_stock' => 'required | boolean',
            'category_id' => 'required',
            'user_id' => 'required',
            'description' => 'required | max:1000',
            'price' => 'required | numeric | min:100 | max:1000000',
            'images.*' => 'file | mimes:jpg,png,jpeg |max:512 '
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }
        return [];
    }
    /**
     * Display a listing of all products
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $products = Product::all();
            foreach ($products as $product) {
                $images = Image::where('product_id', $product->id)->get();
                $product->images = $images ? $images : [];
            }
            if ($products) {
                return response(['success' => true, 'count' => count($products), 'products' => $products], 200);
            } else {
                return response(['message' => 'products not found'], 401);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $errors = $this->validateProduct($request);
            if (count($errors)) {
                return response(['validation errors' => $errors, 'request' => $request->name], 422);
            }

            $product = new Product();
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->in_stock = $request->in_stock;
            $product->category_id = $request->category_id;
            $product->user_id = $request->user_id;
            $product->save();

            /* Uploading images to products folder and saving path in images table */
            $areImagesSaved = $this->saveImages($request, $product->id, 'products');
            if (count($areImagesSaved)) {
                return response(['image saving errors' => $areImagesSaved], 422);
            }

            $images = Image::where('product_id', $product->id)->get();
            $product->images = $images ? $images : [];
            return response(['success' => true, 'message' => 'Product added Successfully', 'product' => $product], 201);
        } catch (Exception $e) {
            return response(['exception error' => $e->getMessage()], 500);
        }
    }

    /**  Display the specified product using product id.
     *
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                $images = Image::where('product_id', $id)->get();
                $product->images = $images ? $images : [];
                return response(['message' => 'success', 'product' => $product], 200);
            } else {
                return response(['message' => 'product does not exist'], 404);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $errors = $this->validateProduct($request);
            if (count($errors)) {
                return response(['validation errors' => $errors], 422);
            }

            $product = Product::find($id);
            if (!$product) {
                return response(['message' => 'product does not exist'], 404);
            }

            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->in_stock = $request->in_stock;
            $product->category_id = $request->category_id;
            $product->user_id = $request->user_id;
            $product->save();

            /* Deleting previous product images */
            if ($request->hasFile('images')) {
                $areImagesDeleted = $this->deleteImages($id, json_decode($request->onlyDeleteSelectedImagesArray));
                if (count($areImagesDeleted)) {
                    return response(['errors' => $areImagesDeleted], 422);
                }

                /* Uploading images to products folder and saving path in images table */
                $areImagesSaved = $this->saveImages($request, $id, 'products');
                if (count($areImagesSaved)) {
                    return response(['errors' => $areImagesSaved], 422);
                }
            }
            $images = Image::where('product_id', $id)->get();
            $product->images = $images ? $images : [];

            return response(['success' => true, 'message' => 'Product updated Successfully', 'product' => $product], 200);

        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 503);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if ($product) {
                // deleting all product images
                $areImagesDeleted = $this->deleteImages($id);
                if (count($areImagesDeleted)) {
                    return response(['errors' => $areImagesDeleted], 422);
                }

                $product->delete();
                return response(['success' => true, 'message' => 'Product deleted Successfully'], 200);
            } else {
                return response(['message' => 'product does not exist'], 404);
            }
        } catch (Exception $e) {
            return response(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}