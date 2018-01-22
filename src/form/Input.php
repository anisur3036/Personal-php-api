<?php
namespace Anis\Form;

class Input
{
    public static function exists($type = 'post')
    {
        switch ($type) {
        	case 'post':
        		return ! empty($_POST);
        		break;
        	case 'get':
        		return ! empty($_GET);
        		break;
        	default:
        	return false;
        }
    }

    public static function get($item)
    {	
    	if (isset($_POST[$item]))
    	{
    		return $_POST[$item];
    	}
    	if (isset($_POST[$item]))
    	{
    		return $_GET[$item];
    	}
    }	
}
