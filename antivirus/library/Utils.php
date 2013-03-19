<?php
class Utils
{
	public static function formatBytes($size, $precision = 2)
	{
	    $base = log($size) / log(1024);
	    $suffixes = array(' bytes', 'k', 'M', 'G', 'T');   
	
	    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	}
}