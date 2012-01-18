CodeIgniter Minify
------------------

A library that provides minify functions using CodeIgniter drivers. The library detects the file extension and will call the right driver to minify that file. By default a javascript en css driver is included, but custom drivers can be added.

Installation
============

Place the Minify folder in your application's libraries folder.

Usage
=====

Use direct drivers access to minify content:

	// load the driver
	$this->load->driver('minify');
	
	// minify javascript content
	$minified = $this->minify->js->min($content);
	
	// minify css content
	$minified = $this->minify->css->min($content);
	
If you want to minify an existing file you can use the global `min()` function. Using the file's extension the correct driver is selected.

	// load the driver
	$this->load->driver('minify');
	
	// minify a file
	$minified = $this->minify->min('path/to/file');
	
If you want to minify an existing file, and automatically create a cache file you can use the global `cache()` function. This function returns the location of the cached file.

	// load the driver
	$this->load->driver('minify');
	
	// minify a file
	$path = $this->minify->cache('path/to/file');
	
You can use this list function in your view files like this:

	<link rel="stylesheet" href="<?php echo $this->minify->cache(base_url('css/stylesheet.css')); ?>" />
	<script type="text/javascript" src="<?php echo $this->minify->cache(base_url('javascript/script.js')); ?>"></script>