<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Statement;
use TRP\BeaverQuery\Expression\{Expression, Identifier, ExpressionAlias, ExpressionList, Operation, FunctionCall, BooleanAnd};
use TRP\BeaverQuery\{BeaverQuery,Table,Parser,BeaverQueryException};
use TRP\BeaverQuery\Join\Join as BQJoin;

abstract class Statement {
	protected static string $whitespace = PHP_EOL;
	public function __toString(): string {
		return $this->print().';';
	}

	abstract public function print(): string;

	public function __isset($key){
		return is_callable([$this,"{$key}_proxy"]);
	}

	public function __get($key){
		$proxy_func = "{$key}_proxy";
		if(is_callable([$this,$proxy_func])){
			return $this->$proxy_func();
		}
	}
}

#[\Property('table', '\TRP\BeaverQuery\Table', set: false)]
trait TableStatement {
	protected Table $table;

	public function table_proxy(): Table {
		return $this->table;
	}
}

trait Join {
	protected array $joins = [];
	protected array $used_aliases;
	public function join(BQJoin $join): BQJoin {
		if(!isset($this->used_aliases)){
			$this->used_aliases = [$this->table->get_name()->alias()];
		}
		$this->used_aliases[] = $join->table->get_name()->alias($this->used_aliases);
		$this->joins[] = $join;
		return $join;
	}

	public function left_join($table, $condition): BQJoin {
		return $this->join(BQJoin::left_join($table, $condition));
	}

	public function right_join($table, $condition): BQJoin {
		return $this->join(BQJoin::right_join($table, $condition));
	}

	public function inner_join($table, $condition = null): BQJoin {
		return $this->join(BQJoin::inner_join($table, $condition));
	}
}

#[\Property('where', '\TRP\BeaverQuery\Expression\ExpressionProxy', set: false)]
trait Where {
	protected ?Expression $where = null;
	public function where(...$expr): static {
		if(!isset($this->where)){
			$this->where = BooleanAnd::parse(...$expr);
		} else {
			$this->where = $this->where->and(...$expr);
		}
		return $this;
	}

	public function where_proxy(): ExpressionProxy {
		return new ExpressionProxy($this, 'where');
	}
}

#[\Property('order_by', '\TRP\BeaverQuery\Expression\ExpressionProxy', set: false)]
trait OrderBy {
	protected ?ExpressionList $orderby = null;
	public function order_by(...$expr): static {
		if(!isset($this->orderby)){
			$this->orderby = ExpressionList::parse_ordering(...$expr);
		} else {
			$this->orderby->add(...$expr);
		}
		return $this;
	}

	public function order_by_proxy(): ExpressionProxy {
		return new ExpressionProxy($this, 'order_by');
	}
}

trait Limit {
	protected ?int $limit = null;
	public function limit(?int $limit): static {
		$this->limit = $limit;
		return $this;
	}
}

trait Offset {
	protected ?int $offset = null;
	public function offset(?int $offset): static {
		$this->offset = $offset;
		return $this;
	}
}

#[\Property('group_by', '\TRP\BeaverQuery\Expression\ExpressionProxy', set: false)]
#[\Property('having', '\TRP\BeaverQuery\Expression\ExpressionProxy', set: false)]
trait GroupBy {
	protected ?ExpressionList $groupby = null;
	protected ?Expression $having = null;

	public function group_by(...$expr): static {
		if(!isset($this->groupby)){
			$this->groupby = ExpressionList::parse(...$expr);
		} else {
			$this->groupby->add(...$expr);
		}
		return $this;
	}

	public function group_by_proxy(): ExpressionProxy {
		return new ExpressionProxy($this, 'group_by');
	}

	public function having(...$expr): static {
		if(!isset($this->having)){
			$this->having = BooleanAnd::parse(...$expr);
		} else {
			$this->having->and($expr);
		}
		return $this;
	}

	public function having_proxy(): ExpressionProxy {
		return new ExpressionProxy($this, 'having');
	}
}

trait ColumnAssignment {
	protected ?ExpressionList $column_assignment = null;

	public function set(...$values): static {
		$this->parse_assignment_list($values);
		return $this;
	}

	protected function parse_assignment_list(array $values, $list_name = 'column_assignment'){
		if(Parser::is_pair($values)){
			$values = [$values[0], $values[1]];
		} else {
			$values = Parser::list($values);
		}
		$assignment = [];
		foreach($values as $key => $value){
			if(is_numeric($key) && Parser::is_pair($value)){
				$assignment[] = Operation::assignment(Identifier::parse_strict($value[0]), Expression::parse($value[1]));
			} else {
				$assignment[] = Operation::assignment(Identifier::parse_strict($key), Expression::parse($value));
			}
		}
		$this->add_assignment_list($list_name, ...$assignment);
	}

	protected function add_assignment_list($list_name, Operation ...$assignment){
		if(!isset($this->$list_name) && !empty($assignment)){
			$this->$list_name = new ExpressionList(...$assignment);
		} else {
			$this->$list_name->add(...$assignment);
		}
	}
}
