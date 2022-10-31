<?php

namespace App\Http\Controllers\Admin\Api;

use App\BranchCategory;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\Category;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchCategoryController extends Controller
{


  public function show()
  {
    $id = request('branch');
    $branchCategories = BranchCategory::where('branch_id', $id)
      ->select('category_id')->get()->pluck('category_id');
    $branch = Branch::findOrFail($id);
    $categories = Category::with('childes')
    ->where('parent_id', 0)
    ->get();
    return response()->json([
      'view' => view('admin-views.branch.partials.show-branch-categories', compact("branchCategories", "categories", "branch"))
        ->render(),
      'categories' => $categories,
      'branchCategories' => $branchCategories,
    ]);
  }

  public function update(Request $request)
  {
    $branch = $request->branch_id;
    $tree = $this->generateBranchTree($branch, $request->category);
    $this->attachBranchCategories($branch, $tree['added']);
    $this->DropBranchCategories($branch, $tree['deleted']);

    Toastr::success('Branch categories updated');

    return back();
  }

  private function generateBranchTree($branch, $categories)
  {
    $current = BranchCategory::whereBranchId($branch)
    ->select('category_id')->get()->pluck('category_id')->toArray();
    $new =  array_diff($categories, $current);
    $delete = array_diff($current, $categories);
    return [
      'added' => $new,
      'deleted' => $delete
    ];
  }

  private function attachBranchCategories($branch, $categories)
  {
    $data = [];
    foreach($categories as $category) {
      $data[] = [
        'category_id' => (int)$category,
        'branch_id' => (int)$branch
      ];
    }
    if (count($data)) {
      DB::table('branch_categories')->insert($data);
    }
  }

  private function dropBranchCategories($branch, $categories)
  {
    if (count($categories)) {
      BranchCategory::where('branch_id', $branch)
      ->whereIn('category_id', $categories)
      ->delete();
    }
  }
}
