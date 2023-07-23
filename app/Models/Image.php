<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'images';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['path', 'product_id'];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }



}