<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the category that owns the task.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: Filter tasks by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter tasks by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    /**
     * Scope: Search tasks by title or description
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('title', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
        }
        return $query;
    }

    /**
     * Scope: Get only trashed (soft-deleted) tasks
     */
    public function scopeTrashed($query)
    {
        return $query->onlyTrashed();
    }
}
