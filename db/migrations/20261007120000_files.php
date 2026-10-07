<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Files extends AbstractMigration
{
    public function change(): void
    {
        $files = $this->table('files');
        $files->addColumn('publicId', 'string', ['limit' => 16])
              ->addColumn('userId', 'integer')
              ->addColumn('title', 'string', ['limit' => 100])
              ->addColumn('originalName', 'string', ['limit' => 255])
              ->addColumn('storedName', 'string', ['limit' => 255])
              ->addColumn('mimeType', 'string', ['limit' => 100])
              ->addColumn('fileType', 'string', ['limit' => 10])
              ->addColumn('size', 'biginteger')
              ->addColumn('public', 'boolean', ['default' => true])
              ->addColumn('views', 'integer', ['default' => 0])
              ->addColumn('created', 'biginteger')
              ->addIndex(['publicId'], ['unique' => true])
              ->addIndex(['userId'])
              ->create();
    }
}
