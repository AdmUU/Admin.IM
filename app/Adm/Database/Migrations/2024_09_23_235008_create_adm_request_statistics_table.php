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

class CreateAdmRequestStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adm_request_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('optype', ['ping', 'traceroute', 'speed', 'ip', 'dns', 'whois', 'ssl', 'dwz', 'seo', 'ai', 'tool'])->comment('op type');
            $table->date('date')->comment('Request date');
            $table->integer('year')->comment('Year');
            $table->integer('month')->comment('Month (1-12)');
            $table->integer('day')->comment('Day (1-31)');
            $table->integer('request_count')->default(0)->comment('Number of requests');
            $table->unique(['optype', 'date']);
            $table->index(['optype', 'year'], 'idx_optype_year');
            $table->index(['optype', 'year', 'month'], 'idx_optype_year_month');
            $table->index(['optype', 'year', 'month', 'day'], 'idx_optype_year_month_day');
            $table->comment('API request statistics table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_request_statistics');
    }
}
