<?php

class ZipArchiveEx extends ZipArchive
{

    public function addDir($location, $name)
    {
        $this->addEmptyDir( $name );
        $this->_addDir( $location, $name );

        /*
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($t_path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($t_path) + 1);
                $t_zip->addFile($filePath, $relativePath);
            }
        }
        */
    }

    private function _addDir($location, $name)
    {
        $name .= '/';
        $location .= '/';
        $dir = opendir($location);
        while ( $file = readdir( $dir ) ) {
            if ( $file == '.' || $file == '..' ) continue;
            $do = (filetype($location . $file ) == 'dir' ) ? 'addDir' : 'addFile';
            $this->$do( $location . $file, $name . $file );
        }
    }


}
