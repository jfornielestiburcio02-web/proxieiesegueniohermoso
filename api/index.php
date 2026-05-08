<?php

function fixLinks($html, $base)
{
    return preg_replace_callback(
        '/(href|src|action)=["\'](.*?)["\']/i',
        function($m) use ($base){

            $attr = $m[1];
            $url  = $m[2];

            if(
                str_starts_with($url, 'data:') ||
                str_starts_with($url, 'javascript:') ||
                str_starts_with($url, '#')
            ){
                return $m[0];
            }

            // URL absoluta
            if(preg_match('/^https?:\/\//i', $url)){
                $new = '/?url=' . urlencode($url);
            }

            // URL relativa
            else{

                $full = rtrim($base, '/') . '/' . ltrim($url, '/');

                $new = '/?url=' . urlencode($full);
            }

            return $attr . '="' . $new . '"';

        },
        $html
    );
}

$url = $_GET['url'] ?? '';

if(!$url){

?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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

h1{
    font-size:50px;
    margin-bottom:20px;
}

input{
    width:600px;
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
    padding:14px 30px;
    border:none;
    border-radius:12px;
    background:#3b82f6;
    color:white;
    cursor:pointer;
    font-size:17px;
}

button:hover{
    background:#2563eb;
}

</style>

</head>
<body>

<h1>Mini Proxy</h1>

<form onsubmit="go(event)">

<input
    id="url"
    type="text"
    placeholder="https://poki.com"
>

<br>

<button>
Abrir
</button>

</form>

<script>

function go(e){

    e.preventDefault();

    let url = document.getElementById("url").value.trim();

    if(!url.startsWith("http://") && !url.startsWith("https://")){
        url = "https://" + url;
    }

    location.href = "/?url=" + encodeURIComponent(url);
}

</script>

</body>
</html>
<?php
exit;
}

if(!preg_match('/^https?:\/\//i', $url)){
    $url = 'https://' . $url;
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

$response = curl_exec($ch);

$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

if(!$response){
    die("Error cargando la web");
}

header_remove("X-Frame-Options");
header_remove("Content-Security-Policy");

if($contentType){
    header("Content-Type: " . $contentType);
}

if(str_contains($contentType, 'text/html')){
    $response = fixLinks($response, $url);
}

echo $response;
?>
