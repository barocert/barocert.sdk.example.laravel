<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Linkhub\LinkhubException;
use Linkhub\Kakaocert\BarocertException;
use Linkhub\Kakaocert\KakaocertService;
use Linkhub\Kakaocert\RequestESign;
use Linkhub\Kakaocert\ResultESign;
use Linkhub\Kakaocert\RequestVerifyAuth;
use Linkhub\Kakaocert\ResultVerifyAuth;
use Linkhub\Kakaocert\RequestCMS;
use Linkhub\Kakaocert\ResultCMS;

class KakaocertController extends Controller
{
  public function __construct() {

    // 통신방식 설정
    define('LINKHUB_COMM_MODE', config('kakaocert.LINKHUB_COMM_MODE'));

    // kakaocert 서비스 클래스 초기화
    $this->KakaocertService = new KakaocertService(config('kakaocert.LinkID'), config('kakaocert.SecretKey'));

    // 인증토큰의 IP제한기능 사용여부, 권장(true)
    $this->KakaocertService->IPRestrictOnOff(config('kakaocert.IPRestrictOnOff'));

    // 카카오써트 API 서비스 고정 IP 사용여부, true-사용, false-미사용, 기본값(false)
    $this->KakaocertService->UseStaticIP(config('kakaocert.UseStaticIP'));

    // 로컬시스템 시간 사용 여부 true(기본값) - 사용, false(미사용)
    $this->KakaocertService->UseLocalTimeYN(config('kakaocert.UseLocalTimeYN'));
  }

  // HTTP Get Request URI -> 함수 라우팅 처리 함수
  public function RouteHandelerFunc(Request $request){
    $APIName = $request->route('APIName');
    return $this->$APIName();
  }

  /*
  * 자동이체 출금동의 인증을 요청합니다.
  * - 
  */
  public function RequestCMS(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 출금동의 AppToApp 인증 여부
    // true-App To App 방식, false-Talk Message 방식
    $isAppUseYN = false;

    // 자동이체 출금동의 요청정보 객체
    $RequestCMS = new RequestCMS();
  
    $RequestCMS->requestID = 'kakaocert_202303130000000000000000000001';

    // 수신자 정보(휴대폰번호, 성명, 생년월일)와 Ci 값 중 택일
    $RequestCMS->receiverHP = '01087674117';
    $RequestCMS->receiverName = '이승환';
    $RequestCMS->receiverBirthday = '19930112';
    // $RequestCMS->ci = '';

    $RequestCMS->reqTitle = '인증요청 메시지 제공란"';
    $RequestCMS->expireIn = 1000;
    $RequestCMS->requestCorp = '청구 기관명란';
    $RequestCMS->bankName = '출금은행명란';
    $RequestCMS->bankAccountNum = '9-4324-5117-58';
    $RequestCMS->bankAccountName = '예금주명 입력란';
    $RequestCMS->bankAccountBirthday = '19930112';
    $RequestCMS->bankServiceType = 'CMS'; // CMS, FIRM, GIRO

    // App to App 방식 이용시, 에러시 호출할 URL
    // $RequestCMS->returnURL = 'https://kakao.barocert.com';

    try {
        $result = $this->KakaocertService->requestCMS($clientCode, $RequestCMS, $isAppUseYN);
    }
    catch(BarocertException $ke) {
        $code = $ke->getCode();
        $message = $ke->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestCMS', ['result' => $result]);
  }

  /*
  * 자동이체 출금동의 요청에 대한 서명 상태를 확인합니다.
  * - 
  */
  public function GetCMSState(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 자동이체 출금동의 요청시 반환받은 접수아이디
    $receiptID = '0230309204458000000000000000000000000001';

    try {
      $result = $this->KakaocertService->getCMSState($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetCMSState', ['result' => $result]);
  }

  /*
  * 자동이체 출금동의 서명을 검증합니다.
  * - 
  */
  public function VerifyCMS(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 자동이체 출금동의 요청시 반환받은 접수아이디
    $receiptID = '0230309201738000000000000000000000000001';

    // 출금동의 AppToApp 방식에서 앱스킴으로 반환받은 서명값.
    // Talk Mesage 방식으로 출금동의 요청한 경우 null 처리.
    $signature = null;

    try {
      $result = $this->KakaocertService->verifyCMS($clientCode, $receiptID, $signature);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifyCMS', ['result' => $result]);
  }

  /*
  * 본인인증을 요청합니다.
  * - 
  */
  public function RequestVerifyAuth(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 본인인증 AppToApp 인증 여부
    // true-App To App 방식, false-Talk Message 방식
    $isAppUseYN = false;

    // 본인인증 요청정보 객체
    $RequestVerifyAuth = new RequestVerifyAuth();

    // 요청번호 40자
    $RequestVerifyAuth->requestID = 'kakaocert_202303130000000000000000000001';
    
    // 수신자 정보(휴대폰번호, 성명, 생년월일)와 Ci 값 중 택일
    $RequestVerifyAuth->receiverHP = '01087674117';
    $RequestVerifyAuth->receiverName = '이승환';
    $RequestVerifyAuth->receiverBirthday = '19930112';
    // $RequestVerifyAuth->ci = '';
    
    $RequestVerifyAuth->reqTitle = '인증요청 메시지 제목란';
    $RequestVerifyAuth->expireIn = 1000;
    $RequestVerifyAuth->token = '본인인증요청토큰';

    // App to App 방식 이용시, 에러시 호출할 URL
    // $RequestVerifyAuth->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestVerifyAuth($clientCode, $RequestVerifyAuth, $isAppUseYN);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestVerifyAuth', ['result' => $result]);
  }

  /*
  * 본인인증 요청에 대한 서명 상태를 확인합니다.
  * - 
  */
  public function GetVerifyAuthState(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '0230309201738000000000000000000000000001';

    try {
      $result = $this->KakaocertService->getVerifyAuthState($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetVerifyAuthState', ['result' => $result]);
  }

  /*
  * 본인인증 서명을 검증합니다.
  * - 
  */
  public function VerifyAuth(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '020040000001';

    // 본인인증 요청시 반환받은 접수아이디
    $receiptID = '0230309195728000000000000000000000000001';

    try {
      $result = $this->KakaocertService->verifyAuth($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifyAuth', ['result' => $result]);
  }

  /*
  * 전자서명 서명을 요청(단건)합니다.
  * - 
  */
  public function RequestESign(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 전자서명 AppToApp 인증 여부
    // true-App To App 방식, false-Talk Message 방식
    $isAppUseYN = false;

    // 전자서명 요청정보 객체
    $RequestESign = new RequestESign();

    // 요청번호 40자
    $RequestESign->requestID = 'kakaocert_202303130000000000000000000005';

    // 수신자 정보(휴대폰번호, 성명, 생년월일)와 Ci 값 중 택일
    $RequestESign->receiverHP = '01087674117';
    $RequestESign->receiverName = '이승환';
    $RequestESign->receiverBirthday = '19930112';
    // $RequestESign->ci = '';

    $RequestESign->reqTitle = '전자서명단건테스트';
    $RequestESign->expireIn = 1000;
    $RequestESign->token = '전자서명단건테스트데이터';
    $RequestESign->tokenType = 'TEXT'; // TEXT, HASH

    // App to App 방식 이용시, 에러시 호출할 URL
    // $RequestESign->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestESign($clientCode, $RequestESign, $isAppUseYN);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestESign', ['result' => $result]);
  }

  /*
  * 전자서명 서명을 요청(다건)합니다.
  * - 
  */
  public function BulkRequestESign(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023020000003';

    // 전자서명 AppToApp 인증 여부
    // true-App To App 방식, false-Talk Message 방식
    $isAppUseYN = false;

    // 전자서명 요청정보 객체
    $BulkRequestESign = new BulkRequestESign();

    // 요청번호 40자
    $BulkRequestESign->requestID = 'kakaocert_202303130000000000000000000005';

    // 수신자 정보(휴대폰번호, 성명, 생년월일)와 Ci 값 중 택일
    $BulkRequestESign->receiverHP = '01087674117';
    $BulkRequestESign->receiverName = '이승환';
    $BulkRequestESign->receiverBirthday = '19930112';
    // $BulkRequestESign->ci = '';

    $BulkRequestESign->reqTitle = '전자서명단건테스트';
    $BulkRequestESign->expireIn = 1000;

    $BulkRequestESign->tokens = array();

    $BulkRequestESign->tokens[] = new Tokens();
    $BulkRequestESign->tokens[0]->reqTitle = "전자서명다건문서테스트1";
    $BulkRequestESign->tokens[0]->token = "전자서명다건테스트데이터1";

    $BulkRequestESign->tokens[] = new Tokens();
    $BulkRequestESign->tokens[1]->reqTitle = "전자서명다건문서테스트2";
    $BulkRequestESign->tokens[1]->token = "전자서명다건테스트데이터2";

    $BulkRequestESign->tokenType = 'TEXT'; // TEXT, HASH

    // App to App 방식 이용시, 에러시 호출할 URL
    // $BulkRequestESign->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->bulkRequestESign($clientCode, $BulkRequestESign, $isAppUseYN);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('BulkRequestESign', ['result' => $result]);
  }

  /*
  * 전자서명 서명 상태를 확인(단건)합니다.
  * - 
  */
  public function GetESignState(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023020000003';

    // 전자서명 요청시 반환받은 접수아이디
    $receiptID = '0230310143306000000000000000000000000001';

    try {
      $result = $this->KakaocertService->getESignState($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetESignState', ['result' => $result]);
  }

  /*
  * 전자서명 서명 상태를 확인(다건)합니다.
  * - 
  */
  public function GetBulkESignState(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023020000003';

    // 전자서명 요청시 반환받은 접수아이디
    $receiptID = '0230310143306000000000000000000000000001';

    try {
      $result = $this->KakaocertService->getBulkESignState($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetBulkESignState', ['result' => $result]);
  }

  /*
  * 전자서명 서명을 검증(단건)합니다.
  * - 
  */
  public function VerifyESign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023020000003';

    // 전자서명 요청시 반환받은 접수아이디
    $receiptID = '0230310143306000000000000000000000000001';

    try {
      $result = $this->KakaocertService->verifyESign($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifyESign', ['result' => $result]);
  }

  /*
  * 전자서명 서명을 검증(다건)합니다.
  * - 
  */
  public function BulkVerifyESign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023020000003';

    // 전자서명 요청시 반환받은 접수아이디
    $receiptID = '0230310143306000000000000000000000000001';

    try {
      $result = $this->KakaocertService->bulkVerifyESign($clientCode, $receiptID);
    }
    catch(BarocertException $ke) {
      $code = $ke->getCode();
      $message = $ke->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('BulkVerifyESign', ['result' => $result]);
  }

}
