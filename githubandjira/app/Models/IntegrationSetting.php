<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'github_username',
        'github_token',
        'jira_email',
        'jira_token',
        'jira_domain',
    ];

    // Encrypt / decrypt
    public function getGithubTokenAttribute($value)
    {
        return Crypt::decryptString($value);
    }
    public function setGithubTokenAttribute($value)
    {
        $this->attributes['github_token'] = Crypt::encryptString($value);
    }


    public function getJiraTokenAttribute($value)
    {
        return Crypt::decryptString($value);
    }
    public function setJiraTokenAttribute($value)
    {
        $this->attributes['jira_token'] = Crypt::encryptString($value);
    }

    public function getJiraDomainAttribute($value)
    {
        return Crypt::decryptString($value);
    }
    public function setJiraDomainAttribute($value)
    {
        $this->attributes['jira_domain'] = Crypt::encryptString($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
