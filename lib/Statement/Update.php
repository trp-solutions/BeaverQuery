<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Statement;
use TRP\BeaverQuery\Table;

class Update extends Statement {
	use TableStatement, ColumnAssignment, Join, Where, OrderBy, Limit;

	public function __construct(
		?Table $table = null,
		protected bool $low_priority = false,
		protected bool $ignore = false
	){
		if(isset($table)){
			$this->table = $table;
		}
	}

	public function print(): string {
		$sql = ["UPDATE"];
		if($this->low_priority){
			$sql[] = 'LOW_PRIORITY';
		}
		if($this->ignore){
			$sql[] = 'IGNORE';
		}
		$sql[] = $this->table->get_name()->identifier_with_alias();
		foreach($this->joins as $join){
			$sql[] = (string) $join;
		}
		$sql[] = "SET\n  ".$this->column_assignment;
		if(isset($this->where)){
			$sql[] = "WHERE\n  ".$this->where;
		}
		if(!empty($this->orderby)){
			$sql[] = "ORDER BY ".$this->orderby;
		}
		if(isset($this->limit)){
			$sql[] = "LIMIT ".$this->limit;
		}
		return implode(self::$whitespace, $sql);
	}
}
