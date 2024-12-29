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

class AddLabelEnColumnToSystemDictDataTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('system_dict_data', function (Blueprint $table) {
            $table->string('label_en', 50)->nullable()->after('label')->comment('Label(en)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_dict_data', function (Blueprint $table) {
            if (Schema::hasColumn('system_dict_data', 'label_en')) {
                $table->dropColumn(['label_en']);
            }
        });
    }
}
