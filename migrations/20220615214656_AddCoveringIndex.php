<?php

use Phpmig\Migration\Migration;

class AddCoveringIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer(); 
        // Run down() to cleanup partially committed migration if there is one
        // TODO: Transactions would be better but aren't working in this migration context
        $this->down();
        $container['db']->query("CREATE INDEX payments_covering_invoice_id_amount_IDX USING BTREE ON cpp_interview.payments (invoice_id,amount);");
        
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer(); 
        $container['db']->query("DROP INDEX IF EXISTS payments_covering_invoice_id_amount_IDX ON cpp_interview.payments;");
    }
}
