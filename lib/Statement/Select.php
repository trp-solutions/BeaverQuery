<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Statement;
use TRP\BeaverQuery\Expression\{Identifier, ExpressionAlias};
use TRP\BeaverQuery\{Table,Parser};

class Select extends Statement {
	use TableStatement, Join, Where, OrderBy, GroupBy, Limit, Offset;
	protected array $select_expr = [];

	public function __construct(?Table $table = null, array $values = []){
		if(isset($table)){
			$this->table = $table;
		}
		$this->columns(...$values);
	}

	public function from(string|array|Table $table): Table {
		$this->table = Table::parse($table);
		return $this->table;
	}

	public function columns(...$columns): static {
		foreach(Parser::list($columns) as $column){
			if(is_string($column)){
				$identifier = Parser::dotted_name_with_alias($column);
				if(isset($identifier) && !isset($identifier[2])){
					$table_name ??= $this->table->get_name();
					$this->select_expr[] = Identifier::parse_strict($identifier, $table_name);
					continue;
				}
			}
			$this->select_expr[] = ExpressionAlias::parse($column);
		}
		return $this;
	}

	public function print(): string {
		return $this->print_select($this->select_expr());
	}

	public function to_select_count(): string {
		$statement = $this->print_select('1 as `row`');
		return "SELECT count(*) FROM ($statement) as `result`;";
	}

	protected function print_select(string $raw_select_expr): string {
		$sql = ["SELECT\n  ".$raw_select_expr];
		if(isset($this->table)){
			$sql[] = "FROM\n  ".$this->table->get_name()->identifier_with_alias();
		}
		foreach($this->joins as $join){
			$sql[] = (string) $join;
		}
		if(isset($this->where)){
			$sql[] = "WHERE\n  ".$this->where;
		}
		if(!empty($this->groupby)){
			$sql[] = "GROUP BY ".$this->groupby;
		}
		if(isset($this->having)){
			$sql[] = "HAVING ".$this->having;
		}
		if(!empty($this->orderby)){
			$sql[] = "ORDER BY ".$this->orderby;
		}
		if(isset($this->limit)){
			$sql[] = "LIMIT ".$this->limit;
			if(isset($this->offset)){
				$sql[] = "OFFSET ".$this->offset;
			}
		}
		return implode(self::$whitespace, $sql);
	}

	protected function select_expr(): string {
		$expr = $this->select_expr;
		if(isset($this->table)){
			$expr = array_merge($this->table->get_column_list(), $expr);
		}
		if(!empty($this->joins)){
			$expr = array_merge($expr, ...array_map(fn($join)=>$join->table->get_column_list(), $this->joins));
		}
		return implode(",\n  ",$expr);
	}
}
