<?php
/*
BeaverQuery is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/BeaverQuery/blob/main/LICENSE
*/
require_once "../lib/autoload.php";

use \TRP\BeaverQuery\BeaverQuery as BQ;

BQ::setDefaultDatabase('test');

?>
<style>
	body {

	}
	.example {
		display: flex;
		justify-content: start;
	}

	.example pre {
		border: 1px solid black;
		margin: 1rem 2rem;
		padding: 1rem;
		position: relative;
	}

	.example pre:first-child:after {
		display: block;
		content: '->';
		position: absolute;
		right: -3rem;
		top: calc(50% - 1rem);
		font-size: 2rem;
	}
</style>
<?php

function example($code){
	$sql = '';
	eval("use \TRP\BeaverQuery\BeaverQuery as BQ;".PHP_EOL.$code);
	echo "<div class=example><pre>$code\necho \$sql;</pre><pre>$sql</pre></div>";
}

echo "<h1>Select</h1>";

example(<<<'PHP'
$sql = BQ::select('staff',['id','name','username','birthday']);
PHP);

example(<<<'PHP'
$sql = BQ::select('staff',['id','name','username','birthday']);
$sql->limit(10);
PHP);

example(<<<'PHP'
$sql = BQ::select('staff',['id','name','username','birthday']);
$sql->limit(10);
$sql->offset(20);
PHP);

example(<<<'PHP'
$staff = BQ::table('staff',['id','name','username','birthday']);
$has_birthday = $staff->birthday->eq(BQ::func('TODAY'));
$sql = $staff->select()->where($has_birthday);
PHP);

example(<<<'PHP'
$staff = BQ::table('staff',['id','name','username','birthday']);
$sql = $staff->select();
$sql->where->birthday->eq(BQ::func('TODAY'));
$staff->set_alias('people');
PHP);

example(<<<'PHP'
$january_birthday = BQ::func('MONTH', BQ::name('staff','birthday'))->eq(1);
$sql = BQ::select('staff',['id','name','username','birthday'])->where($january_birthday);
PHP);

example(<<<'PHP'
$client_supplied_string = "Robert'); DROP TABLE Students;-- \x00 \x0A \x0D \x1A \x22 \x27 \x5C \x60";
$name_match = BQ::name('staff','name')->eq($client_supplied_string);
$sql = BQ::select('staff',['id','name','username','birthday'])->where($name_match);
PHP);

example(<<<'PHP'
$sql = BQ::select('staff',['id','name','username']);
$sql->left_join('staff_role',BQ::name('staff','id')->eq(BQ::name('staff_role','id')));
$sql->columns('role');
$sql->order_by(BQ::name('staff','name'), 'ASC');
PHP);

example(<<<'PHP'
$staff = BQ::table('staff');
$staff->columns('id','name','username','birthday');
$role = BQ::table('staff_role');
$role->columns('role');
$sql = $staff->select();
$sql->left_join($role,$role->staff_id->eq($staff->id));
$sql->where($staff->birthday->func('DATE')->eq(BQ::func('TODAY')));
$sql->order_by($staff->name, 'ASC');
PHP);

example(<<<'PHP'
$s = BQ::table('staff')->columns(['id','staff_id'],['name','staff_name'],'username');
$sl = BQ::table('staff_location');
$l = BQ::table('location')->columns(['id','location_id'],['name','location_name']);
$sql = $s->select();
$sql->inner_join($sl, $s->id->eq($sl->staff_id));
$sql->inner_join($l, $l->id->eq($sl->location_id));
PHP);

example(<<<'PHP'
$s = BQ::table('staff')->columns(['id','staff_id'],['name','staff_name'],'username');
$s2 = BQ::table('staff')->columns(['id','staff2_id'],['name','staff2_name'],['username','staff2_username']);
$sql = $s->select();
$sql->inner_join($s2, $s->id->eq($s2->friend_id));
PHP);

example(<<<'PHP'
$sql = BQ::select('staff',['id','name']);
$sql->columns(
	'username',
	'birthday',
	$sql->table->birthday->func('YEAR')->as('birthyear')
);
$sql->where->id->eq(1000);
$sql->where->disabled->eq(0);
$sql->limit(1);
PHP);

example(<<<'PHP'
$sql = BQ::select('staff',['id','name','username','birthday']);
$sql->where->id->in([1000, 1001, 1002, 1003]);
$sql->order_by->birthday->eq(BQ::func('TODAY'));
$sql->order_by->name;
$sql->order_by([$sql->table->id,'DESC']);
PHP);

echo "<h1>Insert</h1>";

example(<<<'PHP'
$sql = BQ::insert(
	'staff',
	['id','name','username','birthday'],
	[1, 'John', 'john', '2000-12-31'],
	[2, 'Jane', 'jane', '2000-06-15']
);
PHP);

example(<<<'PHP'
$sql = BQ::insert('staff');
$sql->set([
	'id'=>1,
	'name'=>'John',
	'username'=>'john',
	'birthday'=>'2000-12-31'
]);
$sql->dupl_values('name','username','birthday');
PHP);

example(<<<'PHP'
$sql = BQ::insert('staff');
$sql->select('trainee', ['name','username','birthday']);
PHP);




echo "<h1>Update</h1>";


example(<<<'PHP'
$sql = BQ::update('staff');
$sql->set(['name'=>'John', 'username'=>'john', 'birthday'=>'2000-12-31']);
$sql->where(BQ::name('id')->is(1));
PHP);

echo "<h1>Delete</h1>";

example(<<<'PHP'
$sql = BQ::delete('staff', BQ::name('id')->is(1));
PHP);