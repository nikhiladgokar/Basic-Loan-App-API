<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->double('principal_amount')->nullable();
            $table->double('interest_rate')->nullable();
            $table->integer('tenure')->comment('The length of the loan in weeks')->nullable();
            $table->double('total_repay_amount')->nullable();
            $table->double('total_intrest')->nullable();
            $table->text('transaction_id')->nullable();
            $table->string('status')->comment('APPROVAL_PENDING, APPROVED, REJECTED, COMPLETED')->nullable();
            $table->date('loan_applied_date')->nullable();
            $table->date('loan_accepted_date')->nullable();
            $table->date('loan_rejected_date')->nullable();
            $table->date('loan_completed_date')->nullable();
            $table->string('loan_agreement')->default('NOT_ACCEPTED')->comment('NOT_ACCEPTED, ACCEPTED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
