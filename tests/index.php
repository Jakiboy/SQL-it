<?php

include('../src/SQLit.php');
include('array.php');

use floatPHP\SQLit;

echo 'Simple Select Method';
$orm = new SQLit($array);
$result = $orm->select(['id','name'])->query();
var_dump( $result );

echo '<hr>';
echo 'Select (*) example';

$orm = new SQLit($array);
$result = $orm->select('*')->query();
var_dump( $result );

echo '<hr>';
echo 'Simple Where Query';

$orm = new SQLit($array);
$result = $orm->select(['id','name','city'])
->where(['city'=>'California'])
->query();
var_dump( $result );

echo '<hr>';
echo 'Complete Where Query (Like)';

$orm = new SQLit($array);
$result = $orm->select(['gender','name'])
  ->where([
    [
      'column' => 'gender',
      'value'  => 'female',
      'link'   => 'LIKE'
    ]
  ])
  ->query();
var_dump( $result );

echo '<hr>';
echo 'Complete Where Query (>)';

$orm = new SQLit($array);
$result = $orm->select(['gender','name','id'])
  ->where([
    [
      'column' => 'id',
      'value'  => 5, // int|string
      'link'   => '>'
    ]
  ])
  ->query();
var_dump( $result );

echo '<hr>';
echo 'Order by `id` desc';

$orm = new SQLit($array);
$result = $orm->select(['id','gender','name'])
  ->where([
    [
		'column' => 'name',
		'value'  => 'Jhon',
		'link'   => 'in' // Contains
    ]
  ])
  ->order('id','desc') // Default ASC
  ->query();
var_dump( $result );

echo '<hr>';
echo 'Limit to 4 results';

$orm = new SQLit($array);
$result = $orm->select(['id','name'])
  ->where([
    [
      'column' => 'id',
      'value'  => 10, 
      'link'   => '!=' // Not equal
    ]
  ])
  ->limit(4)
  ->query();
var_dump( $result );

echo '<hr>';
echo 'Limit to 4 results';

$orm = new SQLit($array);
$result = $orm->select(['gender','name'])
  ->where([
    [
      'column' => 'id',
      'value'  => 5,
      'link'   => '!=' // Not equal
    ]
  ])
  ->random()
  ->query();
var_dump( $result );

echo '<hr>';
echo 'Select distinct';

$orm = new SQLit($array);
$orm->select('name')
    ->distinct('name');
$result = $orm->query();
var_dump( $result );