Customized CodeIgniter bare migration package to support command line migrate and supress to run the up and down method in the migration file.
Added support for .env file config

Requirements
============

- To load .env file "vlucas/phpdotenv" package is required


Installation
============
```
$ composer require php-bread-and-butter/codeignitermigration
```

Usage
=====
```
$ php vendor/php-bread-and-butter/codeignitermigration/ci migration:bare add_new_post_table
```
OR, you can also CD to the package directory.

```
$ cd vendor/php-bread-and-butter/codeignitermigration
$ php ci migration:bare add_new_post_table
```
The above example will create a new migration file application/migrations/{YmdHis}_add_new_post_table.php where {YmdHis} would be the current timestamp. You can also use CamelCase style for migration name such as AddNewPostTable, but the tool will convert it to snake_case form like add_new_post_table. The default generated content would be like this:

```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_New_Post_Table extends CI_Migration
{
    public function up() {
        // this up() migration is auto-generated, please modify it to your needs
        // Drop table 'table_name' if it exists
        $this->dbforge->drop_table('table_name', true);

        // Table structure for table 'table_name'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'MEDIUMINT',
                'constraint' => '8',
                'unsigned' => true,
                'auto_increment' => true
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => false,
            )
        ));
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('table_name');
    }

    public function down()
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->dbforge->drop_table('table_name', true);
    }
}

```
You can modify the file according to your need.

Alternate usage of the command
```
$ php ci migration:bare add_new_column_to_post_table
$ php ci migration:bare modify_post_table
$ php ci migration:bare update_post_table
$ php ci migration:bare rename_post_table
$ php ci migration:bare remove_post_table
```

Now execute migrate 
```
$ php vendor/php-bread-and-butter/codeignitermigration/migrate
```

The migration can be rolled back using 
```
$ php vendor/php-bread-and-butter/codeignitermigration/suppress ${VERSION}
```
Replace ${VERSION} with {YmdHis}, where {YmdHis} would be the timestamp of previous migration file, like 20211002092605