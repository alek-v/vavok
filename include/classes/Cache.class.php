<?php
/**
* \note \n Functions available in cacheclass.php file:
* \n public static get( $key [, $ttl [, $group] ] )
* \n public static save( $key, $data [, $group ] )
* \n public static deleteCache( $key [, $group ] )
* \n public static deleteGroupCache( $group )
* \n private static function isCached( $file [, $ttl ] )
* \n private static function getPath( $key [, $group ])
* \n private static function hash( $key )
* \n private static function hashFolder( $hash )
* \n private static function getGroupPath( $group, $hash )
* \n private static function removeDir( $dir )
*/
define("TPATH", BASEDIR . "used/datacache/");


final class Cache {

	private static $CACHE_EXT = '.cache';
	private static $CACHE_PATH = TPATH;

	/**
	* \note \b This function gets the data from file cache matching key and group (optional)
	* 
	* \param key - named key the data is saved with in file cache
	* \param ttl - (optional) Time validity of the cache
	* \param group - (optional) named group the data is saved with in
	*
	* \return Serialized-Cached-Data
	*/
    final public static function get($key, $ttl=0, $group='') {

        $file = self::getPath($key, $group);
        $file .= '/'.$key.self::$CACHE_EXT;

        if(self::isCached($file, $ttl) === false) return false;

        return unserialize(file_get_contents($file));
    }

	/**
	* \note \b This function saves the data in file cache using key and group (optional)
	* 
	* \param key - named key the data is saved with in file cache
	* \param data - data to be saved in file cache
	* \param group - (optional) named group to save cache with in
	*
	* \return void
	*/
    final public static function save($key, $data, $group = NULL) {

        $dirPath = self::getPath($key, $group);
        @mkdir($dirPath,0755,true);
        $filePath = $dirPath.'/'.$key.self::$CACHE_EXT;

		$f2 = fopen($filePath, 'w');
		flock($f2, LOCK_EX);
		fwrite($f2, serialize($data));
		flock($f2, LOCK_UN);
		fclose($f2);
	}

	/**
	* \note \b This function deletes the cache file matching key and group (optional)
	* 
	* \param key - named key the data is saved with in file cache
	* \param group - (optional) named group the data is saved with in
	*
	* \return true on success, false on failure
	*/
    final public static function deleteCache($key, $group = NULL) {

        $file = self::getPath($key, $group);
        $file .= '/'.$key.self::$CACHE_EXT;
        return unlink($file);
    }

	/**
	* \note \b This function deletes the entire group in the cache
	*
	* \param group - named group the data is saved with in
	*
	* \return void
	*/
    final public static function deleteGroupCache($group) {

        $path = self::$CACHE_PATH.$group.'*';

        foreach( glob($path, GLOB_ONLYDIR) as $dirName ) {
            self::removeDir($dirName);
        }
    }

	/**
	* \note \b This function checks if data file is available in cache and valid with given ttl
	* 
	* \param filepath - whole file path to cache file based on key and group (optional)
	* \param ttl - (optional) Time validity of the cache
	*
	* \return Serialized-Cached-Data
	*/
    static function isCached($file, $ttl=0) {

        $createdTime = @filemtime($file);
        if(!$createdTime)
            return false;

		if($ttl > 0) {
			$expiryTime = $createdTime + $ttl;
			if($expiryTime <= time())
				return false;
		}

        return true;
    }

	/**
	* \note \b This function calculate the cache path based on key and group (optional)
	*
	* \param key - named key the data is saved with in file cache
	* \param group - (optional) named group the saved with in
	*
	* \return full-path-to-cache-directory
	*/
    static function getPath($key, $group=NULL) {
        $hash = self::hash($key);
        $hashFolder = self::hashFolder($hash);

        $path = self::$CACHE_PATH;
        if($group != NULL) {
            $groupPath = self::getGroupPath($group,$hash);
            $path .= $groupPath.'/';
        }

        return $path.$hashFolder;
    }

	/**
	* \note \b This function generates hash based on cache key
	* 
	* \param key - named key the data is saved with in cache
	*
	* \return hash-value
	*/
    static function hash($key) {
        $count = 0;
        $hash = 0;
        $keyLen = strlen($key);
        for ($i=0;$i<$keyLen;$i++)
        { $hash = $hash*23 + ord(substr($key,$i,1)); $hash %= 8000; }
        return $hash;
    }

	/**
	* \note \b This function returns hash folder based on given hash
	* 
	* \param hash - hash key to be used for hash folder
	*
	* \return hash-folder-name-(numeric)
	*/
    static function hashFolder($hash) {
        $hash >>= 3;
        $hash %= 1000;
        return $hash;
    }

	/**
	* \note \b This function returns group folder path with hash key
	* 
	* \param group - named group the cache is saved or to save with in
	* \param hash - hash key to generate the group folder
	*
	* \return group-folder-name
	*/
    static function getGroupPath($group, $hash) {
        return $group.(1+($hash % 8));
    }

	/**
	* \note \b This function removes all sub-directories, files and directory itself within given directory
	* 
	* \param dir - absolute path to directory to be deleted
	*
	* \return void
	*/
    static function removeDir($dir) {
        $files = glob( $dir . '*', GLOB_MARK );
        foreach( $files as $file ){
            if( substr( $file, -1 ) == '/' )
                self::removeDir( $file );
            else
                unlink( $file );
        }
        if (is_dir($dir)) rmdir( $dir );
    }

}

?>
