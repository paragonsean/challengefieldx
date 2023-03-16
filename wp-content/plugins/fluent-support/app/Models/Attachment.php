<?php

namespace FluentSupport\App\Models;

class Attachment extends Model
{
    protected $table = 'fs_attachments';

    protected $hidden = ['full_url', 'file_path'];

    protected $appends = ['secureUrl'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'person_id',
        'conversation_id',
        'file_type',
        'file_path',
        'full_url',
        'title',
        'driver',
        'file_size',
        'status'
    ];

    public static function boot()
    {
        static::creating(function ($model) {
            $uid = wp_generate_uuid4();
            $model->file_hash = md5($uid.mt_rand(0,1000));
        });
    }

    /**
     * Accessor to get dynamic full_name attribute
     * @return string
     */
    public function getSecureUrlAttribute()
    {
        return add_query_arg([
            'fst_file' => $this->file_hash,
            'secure_sign' => md5($this->id . date('YmdH'))
        ], site_url('/index.php'));
    }
}
