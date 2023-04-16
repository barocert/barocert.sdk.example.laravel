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
        <legend>Kakaocert 본인인증 API</legend>
        <ul>
            <li><a href="KakaocertService/RequestIdentity">RequestIdentity</a> - 본인인증 요청</li>
            <li><a href="KakaocertService/GetIdentityStatus">GetIdentityStatus</a> - 본인인증 상태확인</li>
            <li><a href="KakaocertService/VerifyIdentity">VerifyIdentity</a> - 본인인증 검증</li>
        </ul>
    </fieldset>
    
    <fieldset class="fieldset1">
        <legend>Kakaocert 전자서명 API</legend>
        <ul>
            <li><a href="KakaocertService/Sign">Sign</a> - 전자서명 요청(단건)</li>
            <li><a href="KakaocertService/GetSignStatus">GetSignStatus</a> - 전자서명 상태확인(단건)</li>
            <li><a href="KakaocertService/VerifySign">VerifySign</a> - 전자서명 검증(단건)</li>
            <li><a href="KakaocertService/MultiSign">MultiSign</a> - 전자서명 요청(복수)</li>
            <li><a href="KakaocertService/GetMultiSignStatus">GetMultiSignStatus</a> - 전자서명 상태확인(복수)</li>
            <li><a href="KakaocertService/VerifyMultiSign">VerifyMultiSign</a> - 전자서명 검증(복수)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>Kakaocert 출금동의 API</legend>
        <ul>
            <li><a href="KakaocertService/CMS">CMS</a> - 출금동의 요청</li>
            <li><a href="KakaocertService/GetCMSStatus">GetCMSStatus</a> - 출금동의 상태확인</li>
            <li><a href="KakaocertService/verifyCMS">VerifyCMS</a> - 출금동의 검증</li>
        </ul>
    </fieldset>
</div>
<?php phpinfo() ?>
</body>
</html>
