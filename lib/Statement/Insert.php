<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Statement;
use TRP\BeaverQuery\Expression\{Expression, Identifier, ExpressionList, Operation, FunctionCall};
use TRP\BeaverQuery\{BeaverQuery,Table,Parser,BeaverQueryException};

class Insert extends Statement {
	use TableStatement, ColumnAssignment;
	protected ?ExpressionList $duplicate_update_list = null;
	protected ?Select $select = null;
	protected ?array $columns = null;
	protected ?int $column_count = null;
	protected ?array $values_list = null;

	public function __construct(
		protected Table $table,
		protected bool $low_priority = false,
		protected bool $delayed = false,
		protected bool $high_priority = false,
		protected bool $ignore = false
	){

	}

	public function columns(...$columns): static {
		$this->columns = array_map(fn($column) => Identifier::parse_strict($column), Parser::list($columns));
		return $this;
	}

	public function values(...$values): static {
		$values = Parser::list($values);
		$value_count = count($values);
		if(isset($this->column_count) && $this->column_count !== $value_count){
			throw new BeaverQueryException("Failed matching $value_count value(s) with $this->column_count column(s)");
		} else {
			if(!isset($this->values_list)){
				$this->values_list = [];
			}
			$this->values_list[] = ExpressionList::parse(...$values);
			$this->column_count ??= $value_count;
		}
		return $this;
	}

	public function select($select, $values = []): Select {
		if($select instanceof Select){
			$this->select = $select;
		} else {
			$this->select = BeaverQuery::select($select, $values);
		}
		return $this->select;
	}

	public function dupl_values(...$columns): static {
		$assignment = [];
		foreach($columns as $column_name){
			$column = Identifier::parse_strict($column_name);
			$assignment[] = Operation::assignment($column, FunctionCall::parse('VALUES',$column));
		}
		$this->add_assignment_list('duplicate_update_list', ...$assignment);
		return $this;
	}

	public function dupl(...$values): static {
		$this->parse_assignment_list($values, 'duplicate_update_list');
		return $this;
	}

	public function print(): string {
		$sql = ["INSERT"];
		if($this->high_priority){
			$sql[] = 'HIGH_PRIORITY';
		} elseif($this->delayed){
			$sql[] = 'DELAYED';
		} elseif($this->low_priority){
			$sql[] = 'LOW_PRIORITY';
		}
		if($this->ignore){
			$sql[] = 'IGNORE';
		}
		$sql[] = 'INTO '.$this->table->get_name()->identifier_with_alias();
		$sql = [implode(' ',$sql)];

		if(isset($this->column_assignment)){
			$sql[] = "SET ".$this->column_assignment;
		} elseif(isset($this->values_list)) {
			if(isset($this->columns)){
				$sql[] = '('.implode(',',array_map(fn($col)=>$col->identifier_unqualified(),$this->columns)).')';
			}
			$sql[] = 'VALUES';
			$sql[] = "(".implode("),".self::$whitespace."(",$this->values_list).')';
		} elseif(isset($this->select)){
			$sql[] = $this->select->print();
		} else {
			throw new BeaverQueryException('No values in insert statement');
		}
		if(isset($this->duplicate_update_list)){
			$sql[] = "ON DUPLICATE KEY UPDATE ".$this->duplicate_update_list;
		}
		
		return implode(self::$whitespace, $sql);
	}
}
