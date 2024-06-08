<?php

if (!function_exists('generateToken')) {
    function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}