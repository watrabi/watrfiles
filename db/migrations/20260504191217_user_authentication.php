<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserAuthentication extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
     
    public function change(): void
    {
        $users = $this->table('users');
        $users->addColumn('username', 'string', ['limit' => 20])
              ->addColumn('password', 'string', ['limit' => 255])
              ->addColumn('created', 'biginteger')
              ->addColumn('updated', 'biginteger', ['null' => true])
              ->addIndex(['username'], ['unique' => true])
              ->create();

        $users = $this->table('sessions');
        $users->addColumn('sessionId', 'string', ['limit' => 150])
              ->addColumn('userId', 'integer')
              ->addColumn('userAgent', 'string', ['limit' => 150])
              ->addColumn('created', 'biginteger')
              ->addIndex(['sessionId'], ['unique' => true])
              ->create();
    }
}
