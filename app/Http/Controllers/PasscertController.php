<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Linkhub\LinkhubException;
use Linkhub\Barocert\BarocertException;
use Linkhub\Barocert\PasscertService;
use Linkhub\Barocert\BaseService;
use Linkhub\Barocert\PassIdentity;
use Linkhub\Barocert\PassIdentityVerify;
use Linkhub\Barocert\PassSign;
use Linkhub\Barocert\PassSignVerify;
use Linkhub\Barocert\PassCMS;
use Linkhub\Barocert\PassCMSVerify;
use Linkhub\Barocert\PassLogin;
use Linkhub\Barocert\PassLoginVerify;

class PasscertController extends Controller
{
  public function __construct() {

    // 통신방식 설정
    define('LINKHUB_COMM_MODE', config('barocert.LINKHUB_COMM_MODE'));

    // 패스써트 서비스 클래스 초기화
    $this->PasscertService = new PasscertService(config('barocert.LinkID'), config('barocert.SecretKey'));

    // 인증토큰의 IP제한기능 사용여부, true-사용, false-미사용, 기본값(true)
    $this->PasscertService->IPRestrictOnOff(config('barocert.IPRestrictOnOff'));

    // 패스써트 API 서비스 고정 IP 사용여부, true-사용, false-미사용, 기본값(false)
    $this->PasscertService->UseStaticIP(config('barocert.UseStaticIP'));

    // 로컬시스템 시간 사용여부, true-사용, false-미사용, 기본값(true)
    $this->PasscertService->UseLocalTimeYN(config('barocert.UseLocalTimeYN'));

  }

  // HTTP Get Request URI -> 함수 라우팅 처리 함수
  public function RouteHandelerFunc(Request $request){
    $APIName = $request->route('APIName');
    return $this->$APIName();
  }

  /*
   * 패스 이용자에게 본인인증을 요청합니다.
   * https://developers.barocert.com/reference/pass/php/identity/api#RequestIdentity
   */
  public function RequestIdentity(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 본인인증 요청정보 객체
    $PassIdentity = new PassIdentity();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $PassIdentity->receiverHP = $this->PasscertService->encrypt('01067668440');
    // 수신자 성명 - 80자
    $PassIdentity->receiverName = $this->PasscertService->encrypt('정우석');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $PassIdentity->receiverBirthday = $this->PasscertService->encrypt('19900911');
    
    // 요청 메시지 제목 - 최대 40자
    $PassIdentity->reqTitle = '본인인증 요청 메시지 제목';
    // 요청 메시지 - 최대 500자
    $PassIdentity->reqMessage = $this->PasscertService->encrypt('본인인증 요청 메시지 내용');
    // 고객센터 연락처 - 최대 12자
    $PassIdentity->callCenterNum = '1600-9854';
    // 요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $PassIdentity->expireIn = 1000;
    // 서명 원문 - 최대 2,800자 까지 입력가능
    $PassIdentity->token = $this->PasscertService->encrypt('본인인증 요청 토큰');

    // 사용자 동의 필요 여부
    $PassIdentity->userAgreementYN = true;
    // 사용자 정보 포함 여부
    $PassIdentity->receiverInfoYN = true;

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Push 인증방식
    $PassIdentity->appUseYN = false;
     // ApptoApp 인증방식에서 사용
    // 통신사 유형('SKT', 'KT', 'LGU'), 대문자 입력(대소문자 구분)
    // $PassIdentity->telcoType = 'SKT';
    // ApptoApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $PassIdentity->deviceOSType = 'IOS';

    try {
      $result = $this->PasscertService->requestIdentity($clientCode, $PassIdentity);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/RequestIdentity', ['result' => $result]);
  }

  /*
   * 본인인증 요청 후 반환받은 접수아이디로 본인인증 진행 상태를 확인합니다.
   * 상태확인 함수는 본인인증 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 본인인증 요청 함수를 호출한 당일 23시 59분 59초 이후 상태확인 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/identity/api#GetIdentityStatus
   */
  public function GetIdentityStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 본인인증 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000022';

    try {
      $result = $this->PasscertService->getIdentityStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/GetIdentityStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 반환받은 전자서명값(signedData)과 [1. RequestIdentity] 함수 호출에 입력한 Token의 동일 여부를 확인하여 이용자의 본인인증 검증을 완료합니다.
   * 검증 함수는 본인인증 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 본인인증 요청 함수를 호출한 당일 23시 59분 59초 이후 검증 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/identity/api#VerifyIdentity
   */
  public function VerifyIdentity(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023060000044';

    // 본인인증 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000022';

    // 본인인증 검증 요청정보 객체
    $PassIdentityVerify = new PassIdentityVerify();

    $PassIdentityVerify->receiverHP = $this->PasscertService->encrypt('01067668440');
    $PassIdentityVerify->receiverName = $this->PasscertService->encrypt('정우석');

    try {
      $result = $this->PasscertService->verifyIdentity($clientCode, $receiptID, $PassIdentityVerify);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/VerifyIdentity', ['result' => $result]);
  }

  /* 
   * 패스 이용자에게 문서의 전자서명을 요청합니다.
   * https://developers.barocert.com/reference/pass/php/sign/api#RequestSign
   */
  public function RequestSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 전자서명 요청정보 객체
    $PassSign = new PassSign();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $PassSign->receiverHP = $this->PasscertService->encrypt('01067668440');
    // 수신자 성명 - 80자
    $PassSign->receiverName = $this->PasscertService->encrypt('정우석');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $PassSign->receiverBirthday = $this->PasscertService->encrypt('19900911');

    // 요청 메시지 제목 - 최대 40자
    $PassSign->reqTitle = '전자서명 요청 메시지 제목';
    // 요청 메시지 - 최대 500자
    $PassSign->reqMessage = $this->PasscertService->encrypt('전자서명 요청 메시지 내용');
    // 고객센터 연락처 - 최대 12자
    $PassSign->callCenterNum = '1600-9854';
    // 요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $PassSign->expireIn = 1000;
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $PassSign->token = $this->PasscertService->encrypt('전자서명 요청 토큰');
    // 서명 원문 유형
    // 'TEXT' - 일반 텍스트, 'HASH' - HASH 데이터, 'URL' - URL 데이터
    // 원본데이터(originalTypeCode, originalURL, originalFormatCode) 입력시 'TEXT'사용 불가
    $PassSign->tokenType = $this->PasscertService->encrypt('URL');

    // 사용자 동의 필요 여부
    $PassSign->userAgreementYN = true;
    // 사용자 정보 포함 여부
    $PassSign->receiverInfoYN = true;

    // 원본유형코드
    // 'AG' - 동의서, 'AP' - 신청서, 'CT' - 계약서, 'GD' - 안내서, 'NT' - 통지서, 'TR' - 약관
    $PassSign->originalTypeCode = 'TR';
    // 원본조회URL
    $PassSign->originalURL = 'https://www.passcert.co.kr';
    // 원본형태코드
    // ('TEXT', 'HTML', 'DOWNLOAD_IMAGE', 'DOWNLOAD_DOCUMENT')
    $PassSign->originalFormatCode = 'HTML';

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Push 인증방식
    $PassSign->appUseYN = false;
    // ApptoApp 인증방식에서 사용
    // 통신사 유형('SKT', 'KT', 'LGU'), 대문자 입력(대소문자 구분)
    // $PassSign->telcoType = 'SKT';
    // ApptoApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $PassSign->deviceOSType = 'IOS';

    try {
      $result = $this->PasscertService->requestSign($clientCode, $PassSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/RequestSign', ['result' => $result]);
  }

  /*
   * 전자서명 요청 후 반환받은 접수아이디로 인증 진행 상태를 확인합니다.
   * 상태확인 함수는 전자서명 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 전자서명 요청 함수를 호출한 당일 23시 59분 59초 이후 상태확인 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/sign/api#GetSignStatus
   */
  public function GetSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000023';

    try {
      $result = $this->PasscertService->getSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/GetSignStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 검증 함수는 전자서명 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 전자서명 요청 함수를 호출한 당일 23시 59분 59초 이후 검증 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/sign/api#VerifySign
   */
  public function VerifySign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000023';

    // 전자서명 검증 요청정보 객체
    $PassSignVerify = new PassSignVerify();

    $PassSignVerify->receiverHP = $this->PasscertService->encrypt('01067668440');
    $PassSignVerify->receiverName = $this->PasscertService->encrypt('정우석');

    try {
      $result = $this->PasscertService->VerifySign($clientCode, $receiptID, $PassSignVerify);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/VerifySign', ['result' => $result]);
  }
  
  /*
   * 패스 이용자에게 자동이체 출금동의를 요청합니다.
   * https://developers.barocert.com/reference/pass/php/cms/api#RequestCMS
   */
  public function RequestCMS(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 출금동의 요청 정보 객체
    $PassCMS = new PassCMS();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $PassCMS->receiverHP = $this->PasscertService->encrypt('01067668440');
    // 수신자 성명 - 80자
    $PassCMS->receiverName = $this->PasscertService->encrypt('정우석');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $PassCMS->receiverBirthday = $this->PasscertService->encrypt('19900911');

    // 요청 메시지 제목 - 최대 40자
    $PassCMS->reqTitle = '출금동의 요청 메시지 제목';
    // 요청 메시지 - 최대 500자
    $PassCMS->reqMessage = $this->PasscertService->encrypt('출금동의 요청 메시지 내용');
    // 고객센터 연락처 - 최대 12자
    $PassCMS->callCenterNum = '1600-9854';
    // 요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $PassCMS->expireIn = 1000;
    // 사용자 동의 필요 여부
    $PassCMS->userAgreementYN = true;
    // 사용자 정보 포함 여부
    $PassCMS->receiverInfoYN = true;

    // 출금은행명 - 최대 100자
    $PassCMS->bankName = $this->PasscertService->encrypt('국민은행');
    // 출금계좌번호 - 최대 31자
    $PassCMS->bankAccountNum = $this->PasscertService->encrypt('9-****-5117-58');
    // 출금계좌 예금주명 - 최대 100자
    $PassCMS->bankAccountName = $this->PasscertService->encrypt('정우석');
    // 출금유형
    // CMS - 출금동의, OPEN_BANK - 오픈뱅킹
    $PassCMS->bankServiceType = $this->PasscertService->encrypt('CMS'); 
    // 출금액
    $PassCMS->bankWithdraw = $this->PasscertService->encrypt('1,000,000원'); 

    // AppToApp 요청 여부
    // true - AppToApp 인증방식, false - Push 인증방식
    $PassCMS->appUseYN = false; 
    // ApptoApp 인증방식에서 사용
    // 통신사 유형('SKT', 'KT', 'LGU'), 대문자 입력(대소문자 구분)
    // $PassCMS->telcoType = 'SKT';
    // ApptoApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $PassCMS->deviceOSType = 'IOS';

    try {
        $result = $this->PasscertService->requestCMS($clientCode, $PassCMS);
    }
    catch(BarocertException $re) {
        $code = $re->getCode();
        $message = $re->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/RequestCMS', ['result' => $result]);
  }

  /*
   * 자동이체 출금동의 요청 후 반환받은 접수아이디로 인증 진행 상태를 확인합니다.
   * 상태확인 함수는 자동이체 출금동의 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 자동이체 출금동의 요청 함수를 호출한 당일 23시 59분 59초 이후 상태확인 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/cms/api#GetCMSStatus
   */
  public function GetCMSStatus(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023060000044';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000024';

    try {
      $result = $this->PasscertService->getCMSStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/GetCMSStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 검증 함수는 자동이체 출금동의 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 자동이체 출금동의 요청 함수를 호출한 당일 23시 59분 59초 이후 검증 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/cms/api#VerifyCMS
   */
  public function VerifyCMS(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023060000044';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000024';

    // 출금동의 검증 요청정보 객체
    $PassCMSVerify = new PassCMSVerify();

    $PassCMSVerify->receiverHP = $this->PasscertService->encrypt('01067668440');
    $PassCMSVerify->receiverName = $this->PasscertService->encrypt('정우석');

    try {
      $result = $this->PasscertService->verifyCMS($clientCode, $receiptID, $PassCMSVerify);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/VerifyCMS', ['result' => $result]);
  }

  /*
   * 패스 이용자에게 간편로그인을 요청합니다.
   * https://developers.barocert.com/reference/pass/php/login/api#RequestLogin
   */
  public function RequestLogin(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 간편로그인 요청정보 객체
    $PassLogin = new PassLogin();

    // 수신자 휴대폰번호 - 11자 (하이픈 제외)
    $PassLogin->receiverHP = $this->PasscertService->encrypt('01067668440');
    // 수신자 성명 - 80자
    $PassLogin->receiverName = $this->PasscertService->encrypt('정우석');
    // 수신자 생년월일 - 8자 (yyyyMMdd)
    $PassLogin->receiverBirthday = $this->PasscertService->encrypt('19900911');
    
    // 요청 메시지 제목 - 최대 40자
    $PassLogin->reqTitle = '간편로그인 요청 메시지 제목';
    // 요청 메시지 - 최대 500자
    $PassLogin->reqMessage = $this->PasscertService->encrypt('간편로그인 요청 메시지 내용');
    // 고객센터 연락처 - 최대 12자
    $PassLogin->callCenterNum = '1600-9854';
    // 요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $PassLogin->expireIn = 1000;
    // 서명 원문 - 최대 2,800자 까지 입력가능
    $PassLogin->token = $this->PasscertService->encrypt('간편로그인 요청 토큰');

    // 사용자 동의 필요 여부
    $PassLogin->userAgreementYN = true;
    // 사용자 정보 포함 여부
    $PassLogin->receiverInfoYN = true;

    // AppToApp 요청 여부
    // true - AppToApp 인증방식, false - Push 인증방식
    $PassLogin->appUseYN = false;
    // ApptoApp 인증방식에서 사용
    // 통신사 유형('SKT', 'KT', 'LGU'), 대문자 입력(대소문자 구분)
    // $PassLogin->telcoType = 'SKT';
    // ApptoApp 인증방식에서 사용
    // 모바일장비 유형('ANDROID', 'IOS'), 대문자 입력(대소문자 구분)
    // $PassLogin->deviceOSType = 'IOS';

    try {
      $result = $this->PasscertService->requestLogin($clientCode, $PassLogin);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/RequestLogin', ['result' => $result]);
  }
  
  /*
   * 간편로그인 요청 후 반환받은 접수아이디로 진행 상태를 확인합니다.
   * 상태확인 함수는 간편로그인 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 간편로그인 요청 함수를 호출한 당일 23시 59분 59초 이후 상태확인 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/login/api#GetLoginStatus
   */
  public function GetLoginStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드 (파트너 사이트에서 확인가능)
    $clientCode = '023060000044';

    // 간편로그인 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000025';

    try {
      $result = $this->PasscertService->getLoginStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/GetLoginStatus', ['result' => $result]);
  }

  /*
   * 완료된 전자서명을 검증하고 전자서명값(signedData)을 반환 받습니다.
   * 검증 함수는 간편로그인 요청 함수를 호출한 당일 23시 59분 59초까지만 호출 가능합니다.
   * 간편로그인 요청 함수를 호출한 당일 23시 59분 59초 이후 검증 함수를 호출할 경우 오류가 반환됩니다.
   * https://developers.barocert.com/reference/pass/php/login/api#VerifyLogin
   */
  public function VerifyLogin(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023060000044';

    // 간편로그인 요청시 반환된 접수아이디
    $receiptID = '02308220230600000440000000000025';

    // 간편로그인 검증 요청정보 객체
    $PassLoginVerify = new PassLoginVerify();

    $PassLoginVerify->receiverHP = $this->PasscertService->encrypt('01067668440');
    $PassLoginVerify->receiverName = $this->PasscertService->encrypt('정우석');

    try {
      $result = $this->PasscertService->VerifyLogin($clientCode, $receiptID, $PassLoginVerify);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('PassCert/VerifyLogin', ['result' => $result]);
  }
}
