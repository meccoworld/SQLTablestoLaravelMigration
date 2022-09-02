<?php
$instance = new MigrationGenerator();
$instance->SQLtoMigration();

Class MigrationGenerator{
public static $database = 'bid_eval_db';
public static $headings = 'Tables_in_'.'bid_eval_db';

public function SQLtoMigration(){
	$conn = mysqli_connect('localhost', 'root', 'Sn123456', MigrationGenerator::$database);
	$getTables = mysqli_query($conn, "SHOW TABLES");
	while($setTableRoot = mysqli_fetch_assoc($getTables)){
		$getTable = mysqli_query($conn, "DESC {$setTableRoot[MigrationGenerator::$headings]} ");
		$tablename = $setTableRoot[MigrationGenerator::$headings];
		echo "Generating migration - ".$tablename."\n";
		if($tablename == 'migrations' || $tablename == '')
			continue;

		$ClassModelTable = '';
		$exlodeTable = explode('_', $tablename);
		foreach ($exlodeTable as $key => $value) {
			$ClassModelTable =  $ClassModelTable.ucfirst($value);
		}

		$arguments = "";
		while($setTable = mysqli_fetch_assoc($getTable)){
			$nullable = '';
			$decimals = '';
			$default = '';
			$field = trim($setTable['Field']);
			$type = trim($setTable['Type']);
			$null = trim($setTable['Null']);
			$default = trim($setTable['Default']);
			if($field == 'id' || $field == 'created_at' || $field == 'updated_at')
				continue;
			if($null == 'YES')
				$nullable = '->nullable()';
			if(strpos($type, 'decimal') !== false)
				$decimals = ', 10, 2';
			if(strlen($default) > 0)
				$default = "->default('$default')";
			$arguments .= "\t\t\t\t".'$table->'.MigrationGenerator::resolveType($type)."('".$field."'{$decimals}){$nullable}{$default};\n";
		}
		$file = fopen('migrations/'.date('Y_m_d_His').'_create_'.$setTableRoot[MigrationGenerator::$headings].'_table.php','w');
		$content = str_replace('$columns', $arguments, MigrationGenerator::template());
		$content = str_replace('$tableName', $tablename, $content);
		$content = str_replace('$ClassModelTable', $ClassModelTable, $content);
		
		fwrite($file, $content);
		fclose($file);
	}
}

public static function resolveType($parameter){
	switch($parameter) {
		case strpos($parameter, 'int'):
			return 'integer'; 
		break;
		case strpos($parameter, 'varchar'):
			return 'string'; 
		break;
		case strpos($parameter, 'text'):
			return 'text'; 
		break;
		case strpos($parameter, 'decimal'):
			return 'decimal'; 
		break;
		default:
			return 'string'; 
		break;
	}
}

public static function template(){
	return '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create$ClassModelTableTable extends Migration
{
	/**
 * Run the migrations.
 *
 * @return void
 */
	public function up()
	{
    //
		Schema::create(\'$tableName\', function(Blueprint $table){
			$table->increments(\'id\');	
$columns 				$table->timestamps();
			});
		}

		/**
 * Reverse the migrations.
 *
 * @return void
 */
		public function down()
		{
    //
		}
};
		';

	}
}