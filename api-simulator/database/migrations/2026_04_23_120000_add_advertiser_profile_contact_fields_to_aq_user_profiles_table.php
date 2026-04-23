<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('aq_user_profiles', 'skype_address')) {
                $table->string('skype_address', 255)->nullable()->after('mobile_number');
            }

            if (! Schema::hasColumn('aq_user_profiles', 'icq_address')) {
                $table->string('icq_address', 255)->nullable()->after('skype_address');
            }

            if (! Schema::hasColumn('aq_user_profiles', 'jabber_address')) {
                $table->string('jabber_address', 255)->nullable()->after('icq_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aq_user_profiles', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('aq_user_profiles', 'skype_address') ? 'skype_address' : null,
                Schema::hasColumn('aq_user_profiles', 'icq_address') ? 'icq_address' : null,
                Schema::hasColumn('aq_user_profiles', 'jabber_address') ? 'jabber_address' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
