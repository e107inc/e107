<?php
// Multi indice array sort by sweetland@whoadammit.com
	if (!function_exists('asortbyindex')) {
		function asortbyindex($sortarray, $index) {
			$lastindex = count ($sortarray) - 1;
			for ($subindex = 0; $subindex < $lastindex; $subindex++) {
				$lastiteration = $lastindex - $subindex;
				for ($iteration = 0; $iteration < $lastiteration;    $iteration++) {
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
?>