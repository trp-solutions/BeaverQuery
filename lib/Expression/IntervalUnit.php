<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\BeaverQuery\Expression;
use TRP\BeaverQuery\BeaverQueryException;

enum IntervalUnit: string {
	case MICROSECOND = 'MICROSECOND';
	case SECOND = 'SECOND';
	case MINUTE = 'MINUTE';
	case HOUR = 'HOUR';
	case DAY = 'DAY';
	case WEEK = 'WEEK';
	case MONTH = 'MONTH';
	case QUARTER = 'QUARTER';
	case YEAR = 'YEAR';
	case SECOND_MICROSECOND = 'SECOND_MICROSECOND';
	case MINUTE_MICROSECOND = 'MINUTE_MICROSECOND';
	case MINUTE_SECOND = 'MINUTE_SECOND';
	case HOUR_MICROSECOND = 'HOUR_MICROSECOND';
	case HOUR_SECOND = 'HOUR_SECOND';
	case HOUR_MINUTE = 'HOUR_MINUTE';
	case DAY_MICROSECOND = 'DAY_MICROSECOND';
	case DAY_SECOND = 'DAY_SECOND';
	case DAY_MINUTE = 'DAY_MINUTE';
	case DAY_HOUR = 'DAY_HOUR';
	case YEAR_MONTH = 'YEAR_MONTH';

	public static function parse($unit): self {
		if($unit instanceof self){
			$parsed_unit = $unit;
		} else {
			$parsed_unit = self::tryFrom(mb_strtoupper((string)$unit));
		}
		if(!isset($parsed_unit)){
			throw new BeaverQueryException("Invalid Temporal Interval Unit ('$unit')");
		}
		return $parsed_unit;
	}
}
