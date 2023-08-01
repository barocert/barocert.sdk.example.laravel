<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Linkhub\LinkhubException;
use Linkhub\Barocert\KakaocertService;
use Linkhub\Barocert\BaseService;
use Linkhub\Barocert\KakaoSign;
use Linkhub\Barocert\KakaoGetSignStatus;
use Linkhub\Barocert\KakaoIdentity;
use Linkhub\Barocert\KakaoGetIdentityStatus;
use Linkhub\Barocert\KakaoMultiSign;
use Linkhub\Barocert\KakaoGetMultiSignStatus;
use Linkhub\Barocert\KakaoMultiSignTokens;
use Linkhub\Barocert\KakaoCMS;
use Linkhub\Barocert\KakaoGetCMSState;
use Linkhub\Barocert\KakaoLogin;

class KakaocertController extends Controller
{
  public function __construct() {

    // 통신방식 설정
    define('LINKHUB_COMM_MODE', config('kakaocert.LINKHUB_COMM_MODE'));

    // 카카오써트 서비스 클래스 초기화
    $this->KakaocertService = new KakaocertService(config('kakaocert.LinkID'), config('kakaocert.SecretKey'));

    // 인증토큰의 IP제한기능 사용여부, true-사용, false-미사용, 기본값(true)
    $this->KakaocertService->IPRestrictOnOff(config('kakaocert.IPRestrictOnOff'));

    // 카카오써트 API 서비스 고정 IP 사용여부, true-사용, false-미사용, 기본값(false)
    $this->KakaocertService->UseStaticIP(config('kakaocert.UseStaticIP'));

    // 로컬시스템 시간 사용여부, true-사용, false-미사용, 기본값(true)
    $this->KakaocertService->UseLocalTimeYN(config('kakaocert.UseLocalTimeYN'));
  }

  // HTTP Get Request URI -> 함수 라우팅 처리 함수
  public function RouteHandelerFunc(Request $request){
    $APIName = $request->route('APIName');
    return $this->$APIName();
  }

  /*
   * 카카오톡 사용자에게 본인인증 전자서명을 요청합니다.
   * https://developers.barocert.com/reference/kakao/java/identity/api#RequestIdentity
   */
  public function RequestIdentity(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 본인인증 요청정보 객체
    $KakaoIdentity = new KakaoIdentity();

    // 수신자 정보
    // 휴대폰번호, 성명, 생년월일 
    $KakaoIdentity->receiverHP = $this->KakaocertService->encrypt('01012341234');
    $KakaoIdentity->receiverName = $this->KakaocertService->encrypt('홍길동');
    $KakaoIdentity->receiverBirthday = $this->KakaocertService->encrypt('19700101');
    
    // 인증요청 메시지 제목 - 최대 40자
    $KakaoIdentity->reqTitle = '인증요청 메시지 제목란';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $KakaoIdentity->expireIn = 1000;
    // 서명 원문 - 최대 2,800자 까지 입력가능
    $KakaoIdentity->token = $this->KakaocertService->encrypt('본인인증요청토큰');

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Talk Message 인증방식
    $KakaoIdentity->appUseYN = false;

    // App to App 방식 이용시, 에러시 호출할 URL
    // $KakaoIdentity->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestIdentity($clientCode, $KakaoIdentity);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/RequestIdentity', ['result' => $result]);
  }

  /*
   * 본인인증 요청시 반환된 접수아이디를 통해 서명 상태를 확인합니다.
   * https://developers.barocert.com/reference/kakao/java/identity/api#GetIdentityStatus
   */
  public function GetIdentityStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000006';

    try {
      $result = $this->KakaocertService->getIdentityStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/GetIdentityStatus', ['result' => $result]);
  }

  /*
   * 본인인증 요청시 반환된 접수아이디를 통해 본인인증 서명을 검증합니다.
   * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
   * https://developers.barocert.com/reference/kakao/java/identity/api#VerifyIdentity
   */
  public function VerifyIdentity(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023040000001';

    // 본인인증 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000006';

    try {
      $result = $this->KakaocertService->verifyIdentity($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/VerifyIdentity', ['result' => $result]);
  }

  /* 
   * 카카오톡 사용자에게 전자서명을 요청합니다.(단건)
   * https://developers.barocert.com/reference/kakao/java/sign/api-single#RequestSign
   */
  public function RequestSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청정보 객체
    $KakaoSign = new KakaoSign();

    // 수신자 정보
    // 휴대폰번호, 성명, 생년월일
    $KakaoSign->receiverHP = $this->KakaocertService->encrypt('01012341234');
    $KakaoSign->receiverName = $this->KakaocertService->encrypt('홍길동');
    $KakaoSign->receiverBirthday = $this->KakaocertService->encrypt('19700101');

    // 인증요청 메시지 제목 - 최대 40자
    $KakaoSign->reqTitle = '전자서명단건테스트';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $KakaoSign->expireIn = 1000;
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $KakaoSign->token = $this->KakaocertService->encrypt('전자서명단건테스트데이터');
    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $KakaoSign->tokenType = 'TEXT'; // TEXT, HASH

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Talk Message 인증방식
    $KakaoSign->appUseYN = false;

    // App to App 방식 이용시, 에러시 호출할 URL
    // $KakaoSign->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestSign($clientCode, $KakaoSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/RequestSign', ['result' => $result]);
  }

  /*
   * 전자서명 요청에 대한 서명 상태를 확인합니다.
   * https://developers.barocert.com/reference/kakao/java/sign/api-single#GetSignStatus
   */
  public function GetSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000007';

    try {
      $result = $this->KakaocertService->getSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/GetSignStatus', ['result' => $result]);
  }

  /*
   * 전자서명 요청시 반환된 접수아이디를 통해 서명을 검증합니다. (단건)
   * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
   * https://developers.barocert.com/reference/kakao/java/sign/api-single#VerifySign
   */
  public function VerifySign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000007';

    try {
      $result = $this->KakaocertService->VerifySign($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/VerifySign', ['result' => $result]);
  }

  /*
   * 카카오톡 사용자에게 전자서명을 요청합니다.(복수)
   * https://developers.barocert.com/reference/kakao/java/sign/api-multi#RequestMultiSign
   */
  public function RequestMultiSign(){

     // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청정보 객체
    $KakaoMultiSign = new KakaoMultiSign();

    // 수신자 정보
    // 휴대폰번호, 성명, 생년월일
    $KakaoMultiSign->receiverHP = $this->KakaocertService->encrypt('01012341234');
    $KakaoMultiSign->receiverName = $this->KakaocertService->encrypt('홍길동');
    $KakaoMultiSign->receiverBirthday = $this->KakaocertService->encrypt('19700101');

      // 인증요청 메시지 제목 - 최대 40자
    $KakaoMultiSign->reqTitle = '전자서명복수테스트';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $KakaoMultiSign->expireIn = 1000;

    // 개별문서 등록 - 최대 20 건
    // 개별 요청 정보 객체
    $KakaoMultiSign->tokens = array();
    
    $KakaoMultiSign->tokens[] = new KakaoMultiSignTokens();
    // 인증요청 메시지 제목 - 최대 40자
    $KakaoMultiSign->tokens[0]->reqTitle = "전자서명복수문서테스트1";
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $KakaoMultiSign->tokens[0]->token = $this->KakaocertService->encrypt("전자서명복수테스트데이터1");

    $KakaoMultiSign->tokens[] = new KakaoMultiSignTokens();
    // 인증요청 메시지 제목 - 최대 40자
    $KakaoMultiSign->tokens[1]->reqTitle = "전자서명복수문서테스트2";
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $KakaoMultiSign->tokens[1]->token = $this->KakaocertService->encrypt("전자서명복수테스트데이터2");

    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $KakaoMultiSign->tokenType = 'TEXT'; // TEXT, HASH

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Talk Message 인증방식
    $KakaoMultiSign->appUseYN = false;

    // App to App 방식 이용시, 에러시 호출할 URL
    // $KakaoMultiSign->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestMultiSign($clientCode, $KakaoMultiSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/RequestMultiSign', ['result' => $result]);
  }

  /*
   * 전자서명 요청시 반환된 접수아이디를 통해 서명 상태를 확인합니다. (복수)
   * https://developers.barocert.com/reference/kakao/java/sign/api-multi#GetMultiSignStatus
   */
  public function GetMultiSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000008';

    try {
      $result = $this->KakaocertService->getMultiSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/GetMultiSignStatus', ['result' => $result]);
  }

  /*
   * 전자서명 요청시 반환된 접수아이디를 통해 서명을 검증합니다. (복수)
   * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
   * https://developers.barocert.com/reference/kakao/java/sign/api-multi#VerifyMultiSign
   */
  public function VerifyMultiSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023040000001';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000008';

    try {
      $result = $this->KakaocertService->verifyMultiSign($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/VerifyMultiSign', ['result' => $result]);
  }
  
  /*
   * 카카오톡 사용자에게 출금동의 전자서명을 요청합니다.
   * https://developers.barocert.com/reference/kakao/java/cms/api#RequestCMS
   */
  public function RequestCMS(){

      // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
      $clientCode = '023040000001';

      // 출금동의 요청 정보 객체
      $KakaoCMS = new KakaoCMS();

      // 수신자 정보
      // 휴대폰번호, 성명, 생년월일
      $KakaoCMS->receiverHP = $this->KakaocertService->encrypt('01012341234');
      $KakaoCMS->receiverName = $this->KakaocertService->encrypt('홍길동');
      $KakaoCMS->receiverBirthday = $this->KakaocertService->encrypt('19700101');

      // 인증요청 메시지 제목 - 최대 40자
      $KakaoCMS->reqTitle = '인증요청 메시지 제공란';
      // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
      $KakaoCMS->expireIn = 1000;
      // 청구기관명 - 최대 100자
      $KakaoCMS->requestCorp = $this->KakaocertService->encrypt('청구 기관명란');
      // 출금은행명 - 최대 100자
      $KakaoCMS->bankName = $this->KakaocertService->encrypt('출금은행명란');
      // 출금계좌번호 - 최대 32자
      $KakaoCMS->bankAccountNum = $this->KakaocertService->encrypt('9-4324-5117-58');
      // 출금계좌 예금주명 - 최대 100자
      $KakaoCMS->bankAccountName = $this->KakaocertService->encrypt('예금주명 입력란');
      // 출금계좌 예금주 생년월일 - 8자
      $KakaoCMS->bankAccountBirthday = $this->KakaocertService->encrypt('19700101');
      // 출금유형
      // KakaoCMS - 출금동의용, FIRM - 펌뱅킹, GIRO - 지로용
      $KakaoCMS->bankServiceType = $this->KakaocertService->encrypt('CMS'); // CMS, FIRM, GIRO

      // AppToApp 인증요청 여부
      // true - AppToApp 인증방식, false - Talk Message 인증방식
      $KakaoCMS->appUseYN = false; 

      // App to App 방식 이용시, 에러시 호출할 URL
      // $KakaoCMS->returnURL = 'https://kakao.barocert.com';

    try {
        $result = $this->KakaocertService->requestCMS($clientCode, $KakaoCMS);
    }
    catch(BarocertException $re) {
        $code = $re->getCode();
        $message = $re->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/RequestCMS', ['result' => $result]);
  }

  /*
   * 카카오 출금동의 요청시 반환된 접수아이디를 통해 서명 상태를 확인합니다.
   * https://developers.barocert.com/reference/kakao/java/cms/api#GetCMSStatus
   */
  public function GetCMSStatus(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023040000001';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000009';

    try {
      $result = $this->KakaocertService->getCMSStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/GetCMSStatus', ['result' => $result]);
  }

 /*
  * 자동이체 출금동의 요청시 반환된 접수아이디를 통해 서명을 검증합니다.
  * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
  * https://developers.barocert.com/reference/kakao/java/cms/api#VerifyCMS
  */
  public function VerifyCMS(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023040000001';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02308010230400000010000000000009';

    try {
      $result = $this->KakaocertService->verifyCMS($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/VerifyCMS', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명 데이터 전문(signedData)을 반환 받습니다.
   * 카카오 보안정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류가 반환됩니다.
   * 전자서명 완료일시로부터 10분 이내에 검증 API를 호출하지 않으면 오류가 반환됩니다.
   * https://developers.barocert.com/reference/kakao/java/login/api#VerifyLogin
   */
  public function VerifyLogin(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023040000001';

    // 출금동의 요청시 반환된 트랜잭션 아이디
    $txID = '01a1ea2ab9-1b91-427d-9e48-43a0747ee54c';

    try {
      $result = $this->KakaocertService->verifyLogin($clientCode, $txID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('KakaoCert/VerifyLogin', ['result' => $result]);
  }
}
