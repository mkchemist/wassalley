<?php

namespace App;

use App\Model\Branch;
use App\Model\Category;
use Illuminate\Database\Eloquent\Model;

class BranchCategory extends Model
{
  protected $fillable = [
    'category_id',
    'branch_id'
  ];

  public function category()
  {
    return $this->belongsTo(Category::class,'category_id')->with('childs');
  }

  public function branch()
  {
    return $this->belongsTo(Branch::class, 'branch_id');
  }
}
