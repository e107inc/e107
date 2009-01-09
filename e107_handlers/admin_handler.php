<?php
if (!defined('e107_INIT')) { exit; }

// Multi indice array sort by sweetland@whoadammit.com
if (!function_exists('asortbyindex')) {
	function asortbyindex($sortarray, $index) {
		$lastindex = count ($sortarray) - 1;
		for ($subindex = 0; $subindex < $lastindex; $subindex++) {
			$lastiteration = $lastindex - $subindex;
			for ($iteration = 0; $iteration < $lastiteration; $iteration++) {
				$nextchar = 0;
				if (comesafter ($sortarray[$iteration][$index], $sortarray[$iteration + 1][$index])) {
					$temp = $sortarray[$iteration];
					$sortarray[$iteration] = $sortarray[$iteration + 1];
					$sortarray[$iteration + 1] = $temp;
				}
			}
		}
		return ($sortarray);
	}
}

if (!function_exists('comesafter')) {
	function comesafter($s1, $s2) {
		$order = 1;
		if (strlen ($s1) > strlen ($s2)) {
			$temp = $s1;
			$s1 = $s2;
			$s2 = $temp;
			$order = 0;
		}
		for ($index = 0; $index < strlen ($s1); $index++) {
			if ($s1[$index] > $s2[$index]) return ($order);
				if ($s1[$index] < $s2[$index]) return (1 - $order);
			}
		return ($order);
	}
}

if (!function_exists('multiarray_sort')) {
    function multiarray_sort(&$array, $key, $order = 'asc', $natsort = true, $case = true)
    {
        if(!is_array($array)) return $array;

        $order = strtolower($order);
        foreach ($array as $i => $arr)
        {
           $sort_values[$i] = $arr[$key];
        }

        if(!$natsort) ($order=='asc')? asort($sort_values) : arsort($sort_values);
        else
        {
             $case ? natsort($sort_values) : natcasesort($sort_values);
             if($order != 'asc') $sort_values = array_reverse($sort_values, true);
        }
        reset ($sort_values);

        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
             $sorted_arr[] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}
?>