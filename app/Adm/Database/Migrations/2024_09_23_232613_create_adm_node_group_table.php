<?php

declare(strict_types=1);
/**
 * This file is part of Admin.IM
 *
 * @link     https://www.admin.im
 * @github   https://github.com/AdmUU/Admin.IM
 * @contact  dev@admin.im
 * @license  https://github.com/AdmUU/Admin.IM/blob/main/LICENSE
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateAdmNodeGroupTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adm_node_group', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->comment('group name');
            $table->string('name_en', 255)->comment('english name');
            $table->unsignedTinyInteger('enable')->default(1)->comment('0:disabled,1:enabled');
            $table->unsignedTinyInteger('share_type')->default(0)->comment('0:unshare,1:anonymous,2:member');
            $table->unsignedInteger('weight')->default(0)->comment('order weight');
            $table->string('client_id', 8)->unique()->comment('client key');
            $table->string('client_secret', 16)->comment('client secret');
            $table->json('ext')->nullable()->comment('extended info');
            $table->datetimes();
            $table->softDeletes();
            $table->index(['enable', 'client_id'], 'idx_enable_client_id');
            $table->comment('Agent node group table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_node_group');
    }
}
