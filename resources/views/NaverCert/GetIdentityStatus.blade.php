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
            <li>이용기관 코드 (ClientCode) : {{ $result->clientCode }}</li>
            <li>상태 (State) : {{ $result->state }}</li>
            <li>요청 만료시간 (ExpireIn) : {{ $result->expireIn }}</li>
            <li>이용기관 명 (CallCenterName) : {{ $result->callCenterName }}</li>
            <li>이용기관 연락처 (CallCenterNum) : {{ $result->callCenterNum }}</li>
            <li>복귀 URL (ReturnURL) : {{ $result->returnURL }}</li>
            <li>서명만료일시 (ExpireDT) : {{ $result->expireDT }}</li>
            <li>앱스킴 (Scheme): {{ $result->scheme }}</li>
            <li>모바일장비 유형 (DeviceOSType) : {{ $result->deviceOSType }}</li>
            <li>앱사용유무 (AppUseYN) : {{ $result->appUseYN ? 'true' : 'false'}}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>