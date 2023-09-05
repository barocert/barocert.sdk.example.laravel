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
            <li>이용기관 코드 (ClientCode) : {{ $result->clientCode }}</li>
            <li>접수 아이디 (ReceiptID) : {{ $result->receiptID }}</li>
            <li>상태 (State) : {{ $result->state }}</li>
            <li>요청 만료시간 (ExpireIn) : {{ $result->expireIn }}</li>
            <li>이용기관 명 (CallCenterName) : {{ $result->callCenterName }}</li>
            <li>이용기관 연락처 (CallCenterNum) : {{ $result->callCenterNum }}</li>
            <li>인증요청 메시지 제목 (ReqTitle) : {{ $result->reqTitle }}</li>
            <li>인증요청 메시지 (ReqMessage) : {{ $result->reqMessage }}</li>
            <li>서명요청일시 (RequestDT) : {{ $result->requestDT }}</li>
            <li>서명완료일시 (CompleteDT) : {{ $result->completeDT }}</li>
            <li>서명만료일시 (ExpireDT) : {{ $result->expireDT }}</li>
            <li>서명거절일시 (RejectDT) : {{ $result->rejectDT }}</li>
            <li>원문 유형 (TokenType) : {{ $result->tokenType }}</li>
            <li>사용자동의필요여부 (UserAgreementYN) : {{ $result->userAgreementYN }}</li>
            <li>사용자정보포함여부 (ReceiverInfoYN) : {{ $result->receiverInfoYN }}</li>
            <li>통신사 유형 (TelcoType) : {{ $result->telcoType }}</li>
            <li>모바일장비 유형 (DeviceOSType) : {{ $result->deviceOSType }}</li>
            <li>앱스킴 (Scheme): {{ $result->scheme }}</li>
            <li>앱사용유무 (AppUseYN) : {{ $result->appUseYN ? 'true' : 'false'}}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
