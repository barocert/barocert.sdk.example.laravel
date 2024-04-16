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
            <li>이용기관 코드 (ClientCode) : {{ $result->clientCode }}</li>    
            <li>접수 아이디 (ReceiptID) : {{ $result->receiptID }}</li>
            <li>상태 (State) : {{ $result->state }}</li>
            <li>서명요청일시 (RequestDT) : {{ $result->requestDT }}</li>
            <li>서명완료일시 (CompleteDT) : {{ $result->completeDT }}</li>
            <li>서명만료일시 (ExpireDT) : {{ $result->expireDT }}</li>
            <li>서명거절일시 (RejectDT) : {{ $result->rejectDT }}</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
