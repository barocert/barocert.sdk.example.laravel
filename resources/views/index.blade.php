<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/example.css" media="screen"/>
    <title>Barocert Kakao Service PHP Laravel Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">Barocert Kakao Service PHP Laravel Example.</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>전자서명 API</legend>
        <ul>
            <li><a href="Kakaocert/requestESign">RequestESign</a> -전자서명 요청(단건)</li>
            <li><a href="Kakaocert/bulkRequestESign">BulkRequestESign</a> -전자서명 요청(다건)</li>
            <li><a href="Kakaocert/getESignState">GetESignState</a> -전자서명 상태확인(단건)</li>
            <li><a href="Kakaocert/getBulkESignState">GetBulkESignState</a> -전자서명 상태확인(다건)</li>
            <li><a href="Kakaocert/verifyESign">VerifyESign</a> -전자서명 검증(단건)</li>
            <li><a href="Kakaocert/bulkVerifyESign">BulkVerifyESign</a> -전자서명 검증(다건)</li>
        </ul>

        <legend>본인인증 API</legend>
        <ul>
            <li><a href="Kakaocert/requestVerifyAuth">RequestVerifyAuth</a> - 본인인증 요청</li>
            <li><a href="Kakaocert/getVerifyAuthState">GetVerifyAuthState</a> - 본인인증 상태확인</li>
            <li><a href="Kakaocert/verifyAuth">VerifyAuth</a> - 본인인증 검증</li>
        </ul>

        <legend>출금동의 API</legend>
        <ul>
            <li><a href="Kakaocert/requestCMS">RequestCMS</a> - 출금동의 요청</li>
            <li><a href="Kakaocert/getCMSState">GetCMSState</a> - 출금동의 상태확인</li>
            <li><a href="Kakaocert/verifyCMS">VerifyCMS</a> - 출금동의 검증</li>
        </ul>
    </fieldset>
</div>
<?php phpinfo() ?>
</body>
</html>
