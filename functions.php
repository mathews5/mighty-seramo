<?php


function seramo_register_callback($slug, $callback, $expected_parameters=array(), $settings=array()){
	mighty_seramo::register_callback($slug, $callback, $expected_parameters, $settings, 'functions');
}

seramo_register_callback('herpaderp','mysuperdopefunction', array('smeg', 'head'), array());



function mysuperdopefunction(){
	return array(1,2,3,4,5,6,7);
}