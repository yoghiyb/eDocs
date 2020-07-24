<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationshipsToDocumentsTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents_tags', function (Blueprint $table) {
            $table->bigInteger('document_id')->unsigned()->change();
            $table->foreign('document_id')->references('id')->on('documents')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('tag_id')->unsigned()->change();
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents_tags', function (Blueprint $table) {
            $table->dropForeign('documents_tags_document_id_foreign');
            $table->dropIndex('documents_tags_document_id_foreign');
            // $table->bigInteger('document_id')->change();
            $table->dropForeign('documents_tags_tag_id_foreign');
            $table->dropIndex('documents_tags_tag_id_foreign');
        });
    }
}
