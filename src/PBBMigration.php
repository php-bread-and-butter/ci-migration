<?php
require 'CustomCodeIgniter.php';

/**
 * Command-line tool to generate bare CodeIgniter migration file
 *
 *    php ci migration:bare [name]
 *
 * @package     PBBMigration
 * @author      Sithu K. <cithukyaw@gmail.com>
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE
 */

/**
 * CodeIgniter bare migration file generator
 */
class PBBMigration
{
	/** @var string The command name **/
	private $command;
	/** @var string The string used in migration file name **/
	private $name;
	/** @var integer No of arguments passed to script **/
	protected $argc;
	/** @var array Array of arguments passed to script **/
	protected $argv;
	/** @var array Array of commands */
	protected $commands = array(
		'migration:bare',
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $argv;
		$this->argv = array_slice($argv, 1);
		$this->argc = count($this->argv) - 1;

		$this->command = strtolower(array_shift($this->argv));
		if ($this->validateCommand()) {
			$this->name = array_shift($this->argv);
			$this->execute();
		}
	}

	/**
	 * Convert case in name
	 * @param  string $input The string name
	 * @return string
	 */
	public function convertCase($input)
	{
		if (strpos($input, '_') !== false) {
			$words  = explode('_', $input);
			$words[0] = ucfirst($words[0]);
			for ($i = 0; $i < count($words); $i++) {
				$words[$i] = ucfirst(strtolower($words[$i]));
			}
			$output = implode('_', $words);
		} else {
			$output = preg_replace('/[A-Z]/', '_$0', $input);
		}

		$output = ucfirst($output);
		return trim($output, '_');
	}

	/**
	 * Check the command is valid
	 * @return mixed TRUE or die
	 */
	public function validateCommand()
	{
		if (!in_array($this->command, $this->commands)) {
			die('Command is not valid, e.g., php ci migration:bare add_new_post_table');
		}

		return true;
	}

	/**
	 * Guess the table name from command
	 * @return string tableName
	 */
	public function guessTableName($name)
	{
		$cleanup = str_replace(array(
			"Create_",
			"Add_New_",
			"Modify_",
			"Update_",
			"Rename_",
			"Remove_",
			"Table_",
			"Column_",
			"To_",
			"_Table",
		), "", $name);

		return strtolower($cleanup);
	}

	/**
	 * Execute the command
	 * @return boolean TRUE on success; FALSE on failure
	 */
	private function execute()
	{
		if (empty($this->name)) {
			die('Provide your migration name, e.g., php ci migration:bare add_new_post_table');
			exit;
		}

		$name       = $this->convertCase($this->name);
		$version    = date('YmdHis');
		$className  = 'Migration_' . $version . '_' . $name;
		$fileName   = $version . '_' . strtolower($name) . '.php';
		$tableName  = $this->guessTableName($name);
		$fullFileName = APPPATH . '/migrations/' . $fileName;
		$content = $this->createTableSnippet($className, $tableName);
		
		if (strpos($className, 'Add_New_Column') !== false)
		{
			$content = $this->addNewColumnSnippet($className, $tableName);
		}
		elseif (strpos($className, 'Modify_Column') !== false)
		{
			$content = $this->modifyColumnSnippet($className, $tableName);
		}
		
		if (file_put_contents($fullFileName, mb_convert_encoding($content, 'UTF-8'))) {
			echo 'Generated version: ' . $version . "\n";
			echo 'Generated file name: ' . $fileName . "\n";
			return true;
		} else {
			die('Generation failed.');
			return false;
		}
	}

	private function createTableSnippet($className, $tableName) {
		$content = <<<CODE
<?php defined('BASEPATH') or exit('No direct script access allowed');

class $className extends CI_Migration
{
    public function up()
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Drop table 'table_name' if it exists
        \$this->dbforge->drop_table('$tableName', true);

        // Table structure for table 'table_name'
        \$this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => '11',
                'unsigned' => true,
                'auto_increment' => true
            ),
			'modified_date' => array(
				'type' => 'TIMESTAMP',
				'default NULL ON UPDATE CURRENT_TIMESTAMP' => NULL,
			),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true,
            ),
            'status' => array(
                'type' => 'ENUM',
                'constraint' => ['A','P','D'],
                'null' => false,
                'default' => 'P',
            )
        ));
        \$this->dbforge->add_key('id', true);
        if(\$this->dbforge->create_table('$tableName')) {
           echo "\\n\\rTable $this->name migrated.\\n\\r";
        }
    }

    public function down()
    {
        // this down() migration is auto-generated, please modify it to your needs
        \$this->dbforge->drop_table('$tableName', true);
    }
}
CODE;

		return $content;
	}

	private function addNewColumnSnippet($className, $tableName) {
		$content = <<<CODE
<?php defined('BASEPATH') or exit('No direct script access allowed');

class $className extends CI_Migration
{
    public function up()
    {
        if(!\$this->db->field_exists('status', '$tableName')) {

            \$fields = array(
                'status' => array(
                    'type' => 'TINYINT',
                    'constraint' => '4',
                    'null' => false,
                    'default' => '1',
                    'after' => 'ft_report_toc'
                ),
            );

            if(\$this->dbforge->add_column('$tableName', \$fields)) {
                echo "\\n\\rTable $tableName migrated.\\n\\r";
            }            
        }
    }

    public function down()
    {
        \$this->dbforge->drop_column('$tableName', 'status');
    }
}
CODE;

		return $content;
	}

	private function modifyColumnSnippet($className, $tableName) {
		$content = <<<CODE
<?php defined('BASEPATH') or exit('No direct script access allowed');

class $className extends CI_Migration
{
    public function up()
    {
        \$fields = array(
            'status' => array(
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['START', 'SUBMITTED', 'APPROVED'],
                'null' => false,
                'default' => 'START',
            ),
        );
        
        if(\$this->dbforge->modify_column('$tableName', \$fields)) {
           echo "\\n\\rTable $tableName modified.\\n\\r";
        }
    }

    public function down()
    {
        \$fields = array(
            'status' => array(
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['A', 'P', 'D'],
                'null' => false,
                'default' => 'P',
            ),
        );
        \$this->dbforge->modify_column('$tableName', \$fields);
    }
}
CODE;

		return $content;
	}
}
