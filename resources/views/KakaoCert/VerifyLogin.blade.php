<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="/css/example.css" media="screen"/>
    <title>Barocert Laravel Example</title>
</head>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>{{\Request::fullUrl()}}</legend>
        <ul>
            <li>트랜잭션 아이디 (TxID) : {{ $result->txID }}</li>
            <li>상태 (State) : {{ $result->state }}</li>
            <li>전자서명 데이터 (signedData) : {{ $result->signedData }}</li>
            <li>연계정보 (Ci) : {{ $result->ci }}</li>
            <li>수신자 성명 (ReceiverName) : {{ $result->receiverName }}</li>
            <li>수신자 출생년도 (ReceiverYear) : {{ $result->receiverYear }}</li>
            <li>수신자 출생월일 (ReceiverDay) : {{ $result->receiverDay }}</li>
            <li>수신자 휴대폰번호 (ReceiverHP) : {{ $result->receiverHP }}</li>
            <li>수신자 성별 (ReceiverGender) : {{ $result->receiverGender }}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
