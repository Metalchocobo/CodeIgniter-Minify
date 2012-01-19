<?php
/**
 * @name		CodeIgniter Minify
 * @author		Jens Segers
 * @link		http://www.jenssegers.be
 * @license		MIT License Copyright (c) 2011 Jens Segers
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

class Minify extends CI_Driver_Library {

    // if set, it will put all minified cache files in this directory
    // otherwise it will be put in the same directory as the original file
    public $cache_path = '';
    
    // number of seconds a minified file should be cached, default: 1 week
    public $expire = 604800;
    
    // add base path to the cache path
    public $add_base_path = TRUE;
    
    private $ci;
    
    // allowed drivers, for custom drivers: add to this array
    protected $valid_drivers = array('Minify_js', 'Minify_css');
    
    public function __construct() {
        $this->ci = &get_instance();
        $this->ci->load->helper('file');
    }
    
    /**
     * Minify a file, the minified content is returned
     * @param string source
     * @return string minifed
     */
    public function min() {
        $params = func_get_args();
        $resource = array_shift($params);
        
        // determine extension in order to select the correct driver
        $path_info = pathinfo($resource);
        $driver = $path_info['extension'];
        
        // get source code
        $source = @read_file($resource);
        if($source === FALSE)
            show_error("File does not exist: ".$resource);
        
        // add source to params
        array_unshift($params, $source);
        
        // execute driver
        return call_user_func_array(array($this->$driver, 'min'), $params);
    }
    
    /**
     * Minify and cache a file, the location of the cache file will be returned
     * @param string file
     * @return string cache path
     */
    public function cache() {
        $params = func_get_args();
        $resource = reset($params);
        
        // remove the base url for now, we will add it again later
        $resource = str_ireplace($this->ci->config->item('base_url'), '', $resource);
        
        // only cache local files
        if(stristr($resource, 'http://'))
            return $resource;
        
        // generate cache path & filename
        $path_info = pathinfo($resource);
        if($this->cache_path != '') {
            $cache_file = rtrim($this->cache_path, '/') . '/' . $path_info['filename'] . '.min.' . $path_info['extension'];
        }
        else {
            $path_parts = explode('/', $resource);
            array_pop($path_parts); // remove filename from path
            $cache_file = implode('/', $path_parts) . '/' . $path_info['filename'] . '.min.' . $path_info['extension'];
        }
        
        // calculate expired time
        if(is_numeric($this->expire))
            $expired = time() - $this->expire;
        else
            $expired = strtotime($this->expire);
        
        // check if cache file exists or is expired
        if(!file_exists($cache_file) || filemtime($cache_file) < $expired) {
            write_file($cache_file, $this->min($resource));
            
            // something went wrong saving the cache file
            if(!file_exists($cache_file))
                return $resource;
        }
        
        if($this->add_base_path)
            return $this->ci->config->item('base_url') . $cache_file;
        else
            return $cache_file;
    }
    
	/**
     * Delete cache a file
     * @param file
     * @return string
     */
    public function delete($resource) {
        $params = func_get_args();
        $resource = reset($params);
        
        // remove the base url
        $resource = str_ireplace($this->ci->config->item('base_url'), '', $resource);
        
        // can't remove remote resources
        if(stristr($resource, 'http://'))
            return FALSE;
        
        // generate cache path & filename
        $path_info = pathinfo($resource);
        if($this->cache_path != '') {
            $cache_file = rtrim($this->cache_path, '/') . '/' . $path_info['filename'] . '.min.' . $path_info['extension'];
        }
        else {
            $path_parts = explode('/', $resource);
            array_pop($path_parts); // remove filename from path
            $cache_file = implode('/', $path_parts) . '/' . $path_info['filename'] . '.min.' . $path_info['extension'];
        }
        
        // delete the cache file
        if(file_exists($cache_file))
            return @unlink($cache_file);
        
        return FALSE;
    } 
}

abstract class Minify_Driver extends CI_Driver {
    
    /**
     * Driver specific minify function
     * @param string $resource
     */
    abstract public function min($resource);

}