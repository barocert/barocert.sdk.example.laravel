<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Linkhub\LinkhubException;
use Linkhub\Barocert\BarocertException;
use Linkhub\Barocert\NavercertService;
use Linkhub\Barocert\BaseService;
use Linkhub\Barocert\NaverIdentity;
use Linkhub\Barocert\NaverSign;
use Linkhub\Barocert\NaverMultiSign;
use Linkhub\Barocert\NaverMultiSignTokens;

class NavercertController extends Controller
{
  public function __construct() {

    // 통신방식 설정
    define('LINKHUB_COMM_MODE', config('barocert.LINKHUB_COMM_MODE'));

    // 네이버써트 서비스 클래스 초기화
    $this->NavercertService = new NavercertService(config('barocert.LinkID'), config('barocert.SecretKey'));

    // 인증토큰의 IP제한기능 사용여부, true-사용, false-미사용, 기본값(true)
    $this->NavercertService->IPRestrictOnOff(config('barocert.IPRestrictOnOff'));

    // 네이버써트 API 서비스 고정 IP 사용여부, true-사용, false-미사용, 기본값(false)
    $this->NavercertService->UseStaticIP(config('barocert.UseStaticIP'));
  
  }

  // HTTP Get Request URI -> 함수 라우팅 처리 함수
  public function RouteHandelerFunc(Request $request){
    $APIName = $request->route('APIName');
    return $this->$APIName();
  }

  /*
   * 네이버 이용자에게 본인인증을 요청합니다.
   * https://developers.barocert.com/reference/naver/php/identity/api#RequestIdentity
   */
  public function RequestIdentity(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 본인인증 요청정보 객체
    $NaverIdentity = new NaverIdentity();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $NaverIdentity->receiverHP = $this->NavercertService->encrypt('01012341234');
    // 수신자 성명 - 80자
    $NaverIdentity->receiverName = $this->NavercertService->encrypt('홍길동');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $NaverIdentity->receiverBirthday = $this->NavercertService->encrypt('19700101');
    
    // 고객센터 연락처 - 최대 12자
    $NaverIdentity->callCenterNum = '1600-9854';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $NaverIdentity->expireIn = 1000;

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - 푸시(Push) 인증방식
    $NaverIdentity->appUseYN = false;

    // AppToApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    //$NaverIdentity->deviceOSType = 'IOS';

    // AppToApp 방식 이용시, 호출할 URL
    // "http", "https"등의 웹프로토콜 사용 불가
    //$NaverIdentity->returnURL = 'navercert://sign';


    try {
      $result = $this->NavercertService->requestIdentity($clientCode, $NaverIdentity);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/RequestIdentity', ['result' => $result]);
  }

  /*
   * 본인인증 요청 후 반환받은 접수아이디로 본인인증 진행 상태를 확인합니다.
   * https://developers.barocert.com/reference/naver/php/identity/api#GetIdentityStatus
   */
  public function GetIdentityStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 본인인증 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000036';

    try {
      $result = $this->NavercertService->getIdentityStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/GetIdentityStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 반환받은 전자서명값(signedData)과 [1. RequestIdentity] 함수 호출에 입력한 Token의 동일 여부를 확인하여 이용자의 본인인증 검증을 완료합니다.
   * https://developers.barocert.com/reference/naver/php/identity/api#VerifyIdentity
   */
  public function VerifyIdentity(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023090000021';

    // 본인인증 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000036';

    try {
      $result = $this->NavercertService->verifyIdentity($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/VerifyIdentity', ['result' => $result]);
  }

  /* 
   * 네이버 이용자에게 단건(1건) 문서의 전자서명을 요청합니다.
   * https://developers.barocert.com/reference/naver/php/sign/api-single#RequestSign
   */
  public function RequestSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 전자서명 요청정보 객체
    $NaverSign = new NaverSign();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $NaverSign->receiverHP = $this->NavercertService->encrypt('01012341234');
    // 수신자 성명 - 80자
    $NaverSign->receiverName = $this->NavercertService->encrypt('홍길동');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $NaverSign->receiverBirthday = $this->NavercertService->encrypt('19700101');

    // 인증요청 메시지 제목 - 최대 40자
    $NaverSign->reqTitle = '전자서명(단건) 요청 메시지 제목';
    // 고객센터 연락처 - 최대 12자
    $NaverSign->callCenterNum = '1600-9854';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $NaverSign->expireIn = 1000;
    // 요청 메시지 - 최대 500자
    $NaverSign->reqMessage = $this->NavercertService->encrypt('전자서명(단건) 요청 메시지');
    
    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $NaverSign->tokenType = 'TEXT';
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $NaverSign->token = $NavercertService->encrypt('전자서명(단건) 요청 원문');
    // 서명 원문 유형
    // $NaverSign->tokenType = 'HASH';
    // 서명 원문 유형이 HASH인 경우, 원문은 SHA-256, Base64 URL Safe No Padding을 사용
    // $NaverSign->token = $NavercertService->encrypt($NavercertService->sha256_base64url('전자서명(단건) 요청 원문'));

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - 푸시(Push) 인증방식
    $NaverSign->appUseYN = false;

    // AppToApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $NaverSign->deviceOSType = 'IOS';

    // AppToApp 방식 이용시, 호출할 URL
    // "http", "https"등의 웹프로토콜 사용 불가
    // $NaverSign->returnURL = 'navercert://sign';

    try {
      $result = $this->NavercertService->requestSign($clientCode, $NaverSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/RequestSign', ['result' => $result]);
  }

  /*
   * 전자서명(단건) 요청 후 반환받은 접수아이디로 인증 진행 상태를 확인합니다.
   * https://developers.barocert.com/reference/naver/php/sign/api-single#GetSignStatus
   */
  public function GetSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000037';

    try {
      $result = $this->NavercertService->getSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/GetSignStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 네이버 보안정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류가 반환됩니다.
   * https://developers.barocert.com/reference/naver/php/sign/api-single#VerifySign
   */
  public function VerifySign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000037';

    try {
      $result = $this->NavercertService->VerifySign($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/VerifySign', ['result' => $result]);
  }

  /*
   * 네이버 이용자에게 복수(최대 50건) 문서의 전자서명을 요청합니다.
   * https://developers.barocert.com/reference/naver/php/sign/api-multi#RequestMultiSign
   */
  public function RequestMultiSign(){

     // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 전자서명 요청정보 객체
    $NaverMultiSign = new NaverMultiSign();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $NaverMultiSign->receiverHP = $this->NavercertService->encrypt('01012341234');
    // 수신자 성명 - 80자
    $NaverMultiSign->receiverName = $this->NavercertService->encrypt('홍길동');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $NaverMultiSign->receiverBirthday = $this->NavercertService->encrypt('19700101');

    // 인증요청 메시지 제목 - 최대 40자
    $NaverMultiSign->reqTitle = '전자서명(복수) 요청 메시지 제목';
    // 고객센터 연락처 - 최대 12자
    $NaverMultiSign->callCenterNum = '1600-9854';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $NaverMultiSign->expireIn = 1000;
    // 요청 메시지 - 최대 500자
    $NaverMultiSign->reqMessage = $this->NavercertService->encrypt('전자서명(복수) 요청 메시지');

    // 개별문서 등록 - 최대 50 건
    // 개별 요청 정보 객체
    $NaverMultiSign->tokens = array();
    
    $NaverMultiSign->tokens[] = new NaverMultiSignTokens();
    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $NaverMultiSign->tokens[0]->tokenType = 'TEXT'; 
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $NaverMultiSign->tokens[0]->token = $NavercertService->encrypt("전자서명(복수) 요청 원문 1");
    // 서명 원문 유형
    // $NaverMultiSign->tokens[0]->tokenType = 'HASH'; 
    // 서명 원문 유형이 HASH인 경우, 원문은 SHA-256, Base64 URL Safe No Padding을 사용
    // $NaverMultiSign->tokens[0]->token = $NavercertService->encrypt($NavercertService->sha256_base64url("전자서명(복수) 요청 원문 1"));

    $NaverMultiSign->tokens[] = new NaverMultiSignTokens();
    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $NaverMultiSign->tokens[1]->tokenType = 'TEXT'; 
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $NaverMultiSign->tokens[1]->token = $NavercertService->encrypt("전자서명(복수) 요청 원문 2");
    // 서명 원문 유형
    // $NaverMultiSign->tokens[1]->tokenType = 'HASH'; 
    // 서명 원문 유형이 HASH인 경우, 원문은 SHA-256, Base64 URL Safe No Padding을 사용
    // $NaverMultiSign->tokens[1]->token = $NavercertService->encrypt($NavercertService->sha256_base64url("전자서명(복수) 요청 원문 2"));

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - 푸시(Push) 인증방식
    $NaverMultiSign->appUseYN = false;

    // AppToApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $PassIdentity->deviceOSType = 'IOS';

    // AppToApp 방식 이용시, 호출할 URL
    // "http", "https"등의 웹프로토콜 사용 불가
    // $NaverMultiSign->returnURL = 'navercert://sign';

    try {
      $result = $this->NavercertService->requestMultiSign($clientCode, $NaverMultiSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/RequestMultiSign', ['result' => $result]);
  }

  /*
   * 전자서명(복수) 요청 후 반환받은 접수아이디로 인증 진행 상태를 확인합니다.
   * https://developers.barocert.com/reference/naver/php/sign/api-multi#GetMultiSignStatus
   */
  public function GetMultiSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000038';

    try {
      $result = $this->NavercertService->getMultiSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/GetMultiSignStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 네이버 보안정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류가 반환됩니다.
   * https://developers.barocert.com/reference/naver/php/sign/api-multi#VerifyMultiSign
   */
  public function VerifyMultiSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000038';

    try {
      $result = $this->NavercertService->verifyMultiSign($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/VerifyMultiSign', ['result' => $result]);
  }

  /*
   * 네이버 이용자에게 자동이체 출금동의를 요청합니다.
   * https://developers.barocert.com/reference/naver/php/cms/api#RequestCMS
   */
  public function RequestCMS(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 출금동의 요청정보 객체
    $NaverCMS = new NaverCMS();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $NaverCMS->receiverHP = $NavercertService->encrypt('01012341234');
    // 수신자 성명 - 80자
    $NaverCMS->receiverName = $NavercertService->encrypt('홍길동');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $NaverCMS->receiverBirthday = $NavercertService->encrypt('19700101');
    
    // 인증요청 메시지 제목
    $NaverCMS->reqTitle = "출금동의 요청 메시지 제목";
    // 인증요청 메시지
    $NaverCMS->reqMessage = $NavercertService->encrypt("출금동의 요청 메시지");
    // 고객센터 연락처 - 최대 12자
    $NaverCMS->callCenterNum = '1600-9854';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $NaverCMS->expireIn = 1000;

    // 청구기관명
    $NaverCMS->requestCorp = $NavercertService->encrypt("청구기관");
    // 출금은행명
    $NaverCMS->bankName = $NavercertService->encrypt("출금은행");
    // 출금계좌번호
    $NaverCMS->bankAccountNum = $NavercertService->encrypt("123-456-7890");
    // 출금계좌 예금주명
    $NaverCMS->bankAccountName = $NavercertService->encrypt("홍길동");
    // 출금계좌 예금주 생년월일
    $NaverCMS->bankAccountBirthday = $NavercertService->encrypt("19700101");

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - 푸시(Push) 인증방식
    $NaverCMS->appUseYN = false;

    // AppToApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $NaverCMS->deviceOSType = 'IOS';

    // AppToApp 방식 이용시, 호출할 URL
    // "http", "https"등의 웹프로토콜 사용 불가
    // $NaverCMS->returnURL = 'navercert://cms';

    try {
      $result = $this->NavercertService->requestCMS($clientCode, $NaverCMS);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/RequestCMS', ['result' => $result]);
  }

  /*
   * 자동이체 출금동의 요청 후 반환받은 접수아이디로 인증 진행 상태를 확인합니다.
   * https://developers.barocert.com/reference/naver/php/cms/api#GetCMSStatus
   */
  public function GetCMSStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023090000021';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000036';

    try {
      $result = $this->NavercertService->getCMSStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/GetCMSStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 네이버 보안정책에 따라 검증 API는 1회만 호출할 수 있습니다. 재시도시 오류가 반환됩니다.
   * 전자서명 만료일시 이후에 검증 API를 호출하면 오류가 반환됩니다.
   * https://developers.barocert.com/reference/naver/php/cms/api#VerifyCMS
   */
  public function VerifyCMS(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023090000021';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02309050230900000210000000000036';

    try {
      $result = $this->NavercertService->verifyCMS($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('NaverCert/VerifyCMS', ['result' => $result]);
  }

}
