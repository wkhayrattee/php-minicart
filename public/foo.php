<?php
$items = ['A', 'B', 'C', 'D', 'E'];

$input_items = ['D', 'E', 'F', 'A', 'D', 'B', 'X', 'Z', 'z', 'y', 'C', 'A', 'D', 'F', 'E', 'C', 'M', 'N', "s", 't', 'c', 'a'];

$result = compute($input_items);

echo '<pre>';
print_r($result);
echo '<pre>';
print_r($input_items);
echo '<pre>';
print_r(array_count_values($input_items));


function compute(&$input_items)
{
    return sort($input_items, SORT_FLAG_CASE);
}


