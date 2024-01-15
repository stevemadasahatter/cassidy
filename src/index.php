<?PHP

if ($_SERVER['HTTP_USER_AGENT'] == 'GoogleHC/1.0')
{
        header("HTTP/1.1 200 OK");
        exit();
}

?>
