<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
  public function index()
  {
    $branches = Branch::select('id', 'name')->get();

    return $branches;
  }

  public function show(int $id)
  {
    $branch = Branch::findOrFail($id);

    return $branch;
  }
}
