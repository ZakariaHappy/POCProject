<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->text('github_token')->change();
            $table->text('github_repo')->change();
            $table->text('jira_token')->change();
            $table->text('jira_domain')->change();
        });
    }

    public function down()
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('github_token', 255)->change();
            $table->string('github_repo', 255)->change();
            $table->string('jira_token', 255)->change();
            $table->string('jira_domain', 255)->change();
        });
    }
};
