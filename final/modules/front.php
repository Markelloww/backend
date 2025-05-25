<?php

function front_get($request) {
	$c = ['test' => 'ok'];
	return theme('home', $c);
}

// function front_post($request) {
// 	return redirect();
// }