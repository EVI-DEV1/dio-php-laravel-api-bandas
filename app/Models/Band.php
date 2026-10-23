<?php

namespace App\Models;

use Database\Factories\BandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    /** @use HasFactory<BandFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'gender'];

    /** @var list<string> */
    protected $visible = ['id', 'name', 'gender'];
}
