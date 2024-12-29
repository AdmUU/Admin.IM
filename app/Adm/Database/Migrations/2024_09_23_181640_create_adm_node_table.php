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

class CreateAdmNodeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adm_node', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('did')->comment('distributed ID');
            $table->unsignedInteger('adm_node_group_id')->nullable()->comment('Group ID');
            $table->string('name', 255)->comment('node name');
            $table->string('name_en', 255)->comment('english name');
            $table->unsignedTinyInteger('enable')->default(1)->comment('0:disabled,1:enabled');
            $table->unsignedTinyInteger('block')->default(0)->comment('0:normal,1:blocked');
            $table->unsignedInteger('ipv4')->nullable()->comment('IPv4');
            $table->binary('ipv6')->nullable()->comment('IPv6');
            $table->string('country', 2)->comment('country code');
            $table->string('province', 50)->nullable()->comment('province');
            $table->string('region', 50)->comment('region code');
            $table->string('continent', 2)->comment('continent code');
            $table->string('isp', 4)->nullable()->comment('telecom operator');
            $table->string('provider', 50)->nullable()->comment('hosting provider');
            $table->unsignedTinyInteger('online_status')->default(0)->comment('0:offline,1:online');
            $table->unsignedInteger('online_total_time')->default(0)->nullable()->comment('online time');
            $table->timestamp('online_last_time')->nullable()->comment('last online time');
            $table->unsignedInteger('weight')->default(0)->comment('order weight');
            $table->string('version', 40)->nullable()->comment('version');
            $table->unsignedTinyInteger('connection_type')->default(1)->comment('1:own,2:anonymous,3:user');
            $table->bigInteger('sponsor_id')->nullable()->comment('sponsor ID');
            $table->string('sponsor_name', 255)->nullable()->comment('sponsor name');
            $table->string('sponsor_url', 255)->nullable()->comment('sponsor url');
            $table->enum('sponsor_status', ['review', 'approval'])->nullable()->comment('sponsor status');
            $table->string('fingerprint', 64)->nullable()->comment('fingerprint');
            $table->string('auth_code', 255)->nullable()->comment('authorization code');
            $table->json('ext')->nullable()->comment('extended info');
            $table->bigInteger('created_by')->nullable()->comment('creator');
            $table->bigInteger('updated_by')->nullable()->comment('updater');
            $table->timestamp('created_at')->nullable()->comment('Creation time');
            $table->timestamp('updated_at')->nullable()->comment('Update time');
            $table->timestamp('deleted_at')->nullable()->comment('Delete time');
            $table->string('remark', 255)->nullable()->comment('comment');
            $table->index(['enable', 'block', 'auth_code'], 'idx_enable_block_auth');
            $table->index(['enable', 'online_status'], 'idx_enable_online_status');
            $table->index(['enable', 'online_status', 'ipv4'], 'idx_enable_online_status_ipv4');
            $table->index('ipv4', 'idx_ipv4');
            $table->rawIndex('`ipv6`(100)', 'idx_ipv6');
            $table->index('auth_code', 'idx_auth_code');
            $table->comment('Agent node table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_node');
    }
}
