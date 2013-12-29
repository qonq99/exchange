<?php

class m131212_110350_rate_insert_column2 extends CDbMigration
{
	public function up()
	{
            $transaction=$this->getDbConnection()->beginTransaction();
            try
            {
                $this->addColumn('rate', 'post_identity', 'text');
                
                $transaction->commit();
            }
            catch(Exception $e)
            {
                echo "Exception: ".$e->getMessage()."\n";
                $transaction->rollback();
                return false;
            }
	}

	public function down()
	{
		echo "m131212_110350_rate_insert_column2 does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}