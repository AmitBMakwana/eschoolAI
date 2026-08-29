<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->after('tenant_id')->constrained('roles')->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_url')->nullable()->after('password');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('active')->after('avatar_url');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');

            $table->index(['tenant_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['role_id']);
            $table->dropIndex(['tenant_id', 'role_id']);
            $table->dropColumn(['tenant_id', 'role_id', 'phone', 'avatar_url', 'status', 'last_login_at']);
        });
    }
};
