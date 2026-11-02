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
        Schema::create('gnucash_commodities', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('namespace', 50)->default('CURRENCY');
            $table->string('mnemonic', 50);
            $table->string('fullname', 100)->nullable();
            $table->integer('fraction')->default(100);
            $table->timestamps();
        });

        Schema::create('gnucash_accounts', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('name', 255);
            $table->string('account_type', 50)->index(); // ROOT, ASSET, BANK, CASH, LIABILITY, EQUITY, INCOME, EXPENSE, CREDIT
            $table->string('commodity_id', 32)->nullable();
            $table->integer('commodity_scu')->default(100);
            $table->string('code', 50)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('parent_id', 32)->nullable()->index();
            $table->boolean('placeholder')->default(false);
            $table->string('color', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('gnucash_accounts')
                  ->onDelete('set null');
        });

        Schema::create('gnucash_transactions', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('currency', 32)->default('IDR');
            $table->string('num', 255)->nullable()->index();
            $table->dateTime('post_date')->index();
            $table->dateTime('enter_date')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('gnucash_splits', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('transaction_id', 32)->index();
            $table->string('account_id', 32)->index();
            $table->text('memo')->nullable();
            $table->string('action', 255)->nullable();
            $table->char('reconcile_state', 1)->default('n'); // n: not reconciled, c: cleared, y: reconciled
            $table->dateTime('reconcile_date')->nullable();
            $table->decimal('value', 18, 2)->default(0.00); // positive: debit, negative: credit
            $table->decimal('quantity', 18, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('gnucash_transactions')
                  ->onDelete('cascade');

            $table->foreign('account_id')
                  ->references('id')
                  ->on('gnucash_accounts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gnucash_splits');
        Schema::dropIfExists('gnucash_transactions');
        Schema::dropIfExists('gnucash_accounts');
        Schema::dropIfExists('gnucash_commodities');
    }
};
