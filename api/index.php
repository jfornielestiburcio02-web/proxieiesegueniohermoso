<?php

function fixLinks($html, $baseUrl) {

    $parts = parse_url($baseUrl);

    $scheme = $parts['scheme'] ?? 'https';
    $host = $parts['host'] ?? '';

    $root = $scheme . "://" . $host;

    // CSS
    $html = preg_replace(
        '/href="\/(.*?)"/i',
        'href="' . $root . '/$1"',
        $html
    );

    // JS / imágenes
    $html = preg_replace(
        '/src="\/(.*?)"/i',
        'src="' . $root . '/$1"',
        $html
    );

    return $html;
}

$url = $_GET['url'] ?? '';

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
    font-family:Arial;
    background:#0f172a;
    color:white;
}

.topbar{
    background:#111827;
    padding:15px;
    display:flex;
    gap:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.4);
}

input{
    flex:1;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#1e293b;
    color:white;
    outline:none;
    font-size:16px;
}

button{
    padding:12px 20px;
    border:none;
    border-radius:10px;
    background:#3b82f6;
    color:white;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    background:#2563eb;
}

.content{
    padding:0;
}

iframe{
    width:100%;
    height:calc(100vh - 70px);
    border:none;
    background:white;
}

.home{
    display:flex;
    justify-content:center;
    align-items:center;
    height:80vh;
    flex-direction:column;
    text-align:center;
}

h1{
    font-size:50px;
    margin-bottom:10px;
}

p{
    opacity:0.7;
}

</style>
</head>
<body>

<form class="topbar" method="GET">

    <input
        type="text"
        name="url"
        placeholder="https://example.com"
        value="<?= htmlspecialchars($url) ?>"
    >

    <button type="submit">
        Abrir
    </button>

</form>

<div class="content">

<?php

if($url){

    if(!preg_match('/^https?:\/\//', $url)){
        $url = 'https://' . $url;
    }

    $context = stream_context_create([
        "http" => [
            "header" =>
                "User-Agent: Mozilla/5.0\r\n"
        ]
    ]);

    $html = @file_get_contents($url, false, $context);

    if($html){

        $html = fixLinks($html, $url);

        echo $html;

    }else{

        echo '
        <div class="home">
            <h1>Error</h1>
            <p>No se pudo cargar la página.</p>
        </div>
        ';
    }

}else{

    echo '
    <div class="home">
        <h1>Mini Proxy</h1>
        <p>Navega usando la IP del servidor</p>
    </div>
    ';
}

?>

</div>

</body>
</html>
