<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Chat
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chat query()
 * @mixin \Eloquent
 */
class Chat extends Model
{
    use HasFactory;

    protected $primaryKey = 'local_id';

    protected $fillable = [
        'id',
        'username',
        'first_name',
        'user_id',
        'first_name',
        'type',
        'language_id'
    ];

    public function language(){
        return $this->belongsTo(Language::class);
    }

}
