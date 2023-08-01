<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="/css/example.css" media="screen"/>
    <title>Passcert SDK PHP Laravel Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>{{\Request::fullUrl()}}</legend>
        <ul>
            <li>접수아이디 (receiptID) : {{ $result->receiptId }}</li>
            <li>앱스킴 (scheme): {{ $result->scheme }}</li>
            <li>앱다운로드URL (marketUrl): {{ $result->marketUrl }}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
