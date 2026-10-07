<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;

enum BindingStrength: int {
	// Placeholder, for when binding strength hasn't been detected
	case Undefined = 0;
	// = (assignment), :=
	case Assignment = 1;
	// OR, ||
	case Or = 2;
	// XOR
	case Xor = 3;
	// AND, &&
	case And = 4;
	// NOT
	case Not = 5;
	// BETWEEN, CASE, WHEN, THEN, ELSE
	case Case = 6;
	// = (comparison), <=>, >=, >, <=, <, <>, !=, IS, LIKE, REGEXP, IN, MEMBER OF
	case Comparison = 7;
	// |
	case BitwiseOr = 8;
	// &
	case BitwiseAnd = 9;
	// <<, >>
	case Bitshift = 10;
	// -, +
	case Addition = 11;
	// *, /, DIV, %, MOD
	case Multiplication = 12;
	// ^
	case Power = 13;
	// - (unary minus), ~ (unary bit inversion)
	case Invert = 14;
	// !
	case Exclamation = 15;
	// BINARY, COLLATE
	case Collate = 16;
	// INTERVAL
	case Interval = 17;
	// Any constant or identifier
	case Atom = 18;

	public function stronger_than(self $other): bool {
		return $this->value > $other->value;
	}
}
