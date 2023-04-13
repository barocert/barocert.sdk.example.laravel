<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="/css/example.css" media="screen"/>
    <title>Kakaocert SDK PHP Laravel Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>{{\Request::fullUrl()}}</legend>
        <ul>
            <li>접수 아이디 (ReceiptID) : {{ $result->receiptID }}</li>
            

            <?php
                for ($i = 0; $i < Count($result->multiSignedData); $i++) {
            ?>

                <li>전자서명 데이터 전문 (multiSignedData) : {{ $result->multiSignedData[$i] }}</li>

            <?php
                }
            ?>

            <li>연계정보 (Ci) : {{ $result->ci }}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
