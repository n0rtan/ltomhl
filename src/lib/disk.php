<?php

function get_scan_dir()
{
    $mhl_file_path = get_mhl_file_path();

    return get_scan_dir_from_mhl_file_path($mhl_file_path);
}

function get_scan_dir_from_mhl_file_path($mhl_file_path)
{
    return dirname($mhl_file_path);
}

function disk_read(string $file_path, callable $mhl_verify_hash_callback)
{

}