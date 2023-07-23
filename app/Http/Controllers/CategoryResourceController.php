<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryResourceController extends Controller
{
    /**
     * Display a listing of all categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return response(['message' => 'success', 'categories' => $categories]);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Store a newly created category in db.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required | min:3 | max:100',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 422);
            }
            $category = new Category();
            $category->name = $request->name;
            $category->save();

            return response(['message' => 'Category Added Successfully', "category" => $category], 201);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 401);
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
            $category = Category::find($id);
            if ($category) {
                $category->delete();
                return response(['message' => 'Category Deleted Successfully']);
            } else {
                return response(['message' => 'Category Not Found'], 404);
            }
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }
}