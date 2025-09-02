<?php

namespace App\Models;

use App\Models\User;
use App\Enum\BlogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'post', 'title', 'image', 'blog_image', 'description', 'body', 'status', 'meta_title', 'meta_description', 'keywords', 'canonical_url', 'action_by'];

    protected $casts = [
        'status' => BlogStatus::class,
    ];

    public function getBlogImageLinkAttribute()
    {
        if ($this->blog_image) {
            return Storage::disk('public')->url('/' . $this->blog_image);
        }
    }
    public function getImageLinkAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url('/' . $this->image);
        }
    }

    public function actionby(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by', 'id');
    }
}
