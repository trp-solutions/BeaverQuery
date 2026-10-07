<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Join;

enum Type: string {
	case Join = "JOIN";
	case Inner = "INNER JOIN";
	case Cross = "CROSS JOIN";
	case Straight = "STRAIGHT_JOIN";
	case Left = "LEFT JOIN";
	case LeftOuter = "LEFT OUTER JOIN";
	case Right = "RIGHT JOIN";
	case RightOuter = "RIGHT OUTER JOIN";
	case Natural = "NATURAL JOIN";
	case NaturalInner = "NATURAL INNER JOIN";
	case NaturalLeft = "NATURAL LEFT JOIN";
	case NaturalLeftOuter = "NATURAL LEFT OUTER JOIN";
	case NaturalRight = "NATURAL RIGHT JOIN";
	case NaturalRightOuter = "NATURAL RIGHT OUTER JOIN";

	public static function parse(string|self $type): self {
		if($type instanceof self){
			return $type;
		}
		$type_string = strtoupper(trim($type));
		if($type_string == 'STRAIGHT'){
			return self::Straight;
		}
		if(!str_ends_with($type_string, 'JOIN')){
			$type_string .= ' JOIN';
		}
		$enum = self::tryFrom($type_string);
		if(isset($enum)){
			return $enum;
		}
		throw new BeaverQueryException("Unregcognized join type '$type'");
	}

	public function requires_specification(): bool {
		return $this == self::Left
			|| $this == self::LeftOuter
			|| $this == self::Right
			|| $this == self::RightOuter;
	}

	public function allows_specification(): bool {
		return $this->requires_specification()
			|| $this == self::Join
			|| $this == self::Inner
			|| $this == self::Cross
			|| $this == self::Straight;
	}
}
