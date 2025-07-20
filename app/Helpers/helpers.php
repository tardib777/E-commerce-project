<?php
use App\Models\Category;
if (! function_exists('insertCategory')) {
    function insertCategory(...$category){
        $len = count($category);
        for($i = 0; $i < $len; $i++) {
            Category::create(['name' => $category[$i]]);
        }
    }
    
}
if (!function_exists('uploadFile')) {
        function uploadFile($file, $folder, $disk = 'public') {
        $fileName = time() . $file->getClientOriginalName();
        $path = $file->storeAs($folder, $fileName, $disk);
        return $path;
    }
}