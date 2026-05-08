<?php

function proxifyHtml($html, $proxyPrefix)
{
    // href=""
    $html = preg_replace_callback(
        '/href=["\'](.*?)["\']/i',
        function ($m) use ($proxyPrefix) {

            $url = $m[1];

            if (
                str_starts_with($url, '#') ||
                str_starts_with($url, 'javascript:') ||
                str_starts_with($url, 'data:')
            ) {
                return $m[0];
            }

            return 'href="' . $proxyPrefix . $url . '"';
        },
        $html
    );

    // src=""
    $html = preg_replace_callback(
        '/src=["\'](.*?)["\']/i',
        function ($m) use ($proxyPrefix) {

            $url = $m[1];

            if (
                str_starts_with($url, 'data:')
            ) {
                return $m[0];
            }

            return 'src="' . $proxyPrefix . $url . '"';
        },
        $html
    );

    // action=""
    $html = preg_replace_callback(
        '/action=["\'](.*?)["\']/i',
        function ($m) use ($proxyPrefix) {

            $url = $m[1];

            return 'action="' . $proxyPrefix . $url . '"';
        },
        $html
    );

    return $html;
}

$request = $_SERVER['REQUEST_URI'];

$request = trim($request, '/');

$self = basename(__FILE__);

if (str_starts_with($request, $self)) {
    $request = substr($request, strlen($self));
    $request = trim($request, '/');
}

if (!$request) {

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mini Proxy</title>

<style>

body{
    margin:0;
    background:#0f172a;
    color:white;
    font-family:Arial;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    flex-direction:column;
}

input{
    width:500px;
    max-width:90%;
    padding:15px;
    border:none;
    border-radius:12px;
    background:#1e293b;
    color:white;
    font-size:18px;
    outline:none;
}

button{
    margin-top:15px;
    padding:12px 25px;
    border:none;
    border-radius:12px;
    background:#3b82f6;
    color:white;
    font-size:16px;
    cursor:pointer;
}

</style>

</head>
<body>

<h1>Mini Proxy</h1>

<form onsubmit="go(event)">

<input
    id="url"
    type="text"
    placeholder="https://example.com"
>

<button>
Abrir
</button>

</form>

<script>

function go(e){

    e.preventDefault();

    let url = document.getElementById("url").value;

    if(!url.startsWith("http://") && !url.startsWith("https://")){
        url = "https://" + url;
    }

    location.href = "/" + url;
}

</script>

</body>
</html>
<?php
exit;
}

if (
    !str_starts_with($request, 'http://') &&
    !str_starts_with($request, 'https://')
) {
    $request = 'https://' . $request;
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $request);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

$response = curl_exec($ch);

$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

if (!$response) {
    die("Error cargando la web");
}

if ($contentType) {
    header("Content-Type: $contentType");
}

header_remove("X-Frame-Options");
header_remove("Content-Security-Policy");

if (str_contains($contentType, 'text/html')) {

    $proxyPrefix = '/';

    $response = proxifyHtml($response, $proxyPrefix);
}

echo $response;
?>
