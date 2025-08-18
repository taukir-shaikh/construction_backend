<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $title
 * @property string $slug
 * @property string|null $short_desc
 * @property string|null $content
 * @property int $status
 */

class Service extends Model
{
    use HasFactory;
    //

 protected $fillable = [
        'title',
        'slug',
        'short_desc',
        'content',
        'image',
        'status',
    ];
}
