<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CategoryLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;

class CategoryController extends Controller
{
    public function get_categories()
    {
        try {
            $branch = request('branch');
            $categories = Category::where(['position'=>0,'status'=>1])
            ->when($branch, function ($query) use($branch) {
              $query->whereIn('id', function ($sub) use($branch) {
                $sub->from('branch_categories')->select('category_id')
                ->where('branch_id', $branch);
              });
            })
            ->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_childes($id)
    {
        try {
            $branch = request('branch');
            $categories = Category::where(['parent_id' => $id,'status'=>1])
            ->when($branch, function ($query) use($branch) {
              $query->whereIn('id', function ($sub) use($branch) {
                $sub->from('branch_categories')->select('category_id')
                ->where('branch_id', $branch);
              });
            })
            ->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function get_products($id)
    {
        $products = Helpers::product_data_formatting(CategoryLogic::products($id), true);
        foreach($products as $product) {
            $product->amount = $product->unit ? $product->unit->quantity : 1;
        }
        return response()->json($products, 200);
    }

    public function get_all_products($id)
    {
        try {
            return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id), true), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function show($id)
    {
      $category =  Category::where('id', $id)->firstOrFail();

      return $category;
    }
}
