<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'github_username',
        'github_token',
        'github_repo',
        'jira_email',
        'jira_token',
        'jira_domain',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
