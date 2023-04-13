<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Linkhub\LinkhubException;
use App\Library\BarocertException;
use App\Library\KakaocertService;
use App\Library\RequestSign;
use App\Library\GetSignStatus;
use App\Library\RequestIdentity;
use App\Library\RequestMultiSign;
use App\Library\GetCMSState;
use App\Library\MultiSignTokens;
use App\Library\RequestCMS;

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
  * 카카오톡 사용자에게 본인인증 전자서명을 요청합니다.
  */
  public function RequestIdentity(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 본인인증 요청정보 객체
    $RequestIdentity = new RequestIdentity();

    // 수신자 정보
    // 휴대폰번호,성명,생년월일 또는 Ci(연계정보)값 중 택 일
    $RequestIdentity->receiverHP = $this->KakaocertService->encrypt('01054437896');
    $RequestIdentity->receiverName = $this->KakaocertService->encrypt('최상혁');
    $RequestIdentity->receiverBirthday = $this->KakaocertService->encrypt('19880301');
    // $RequestIdentity->ci = $KakaocertService->encrypt('');
    
    // 인증요청 메시지 제목 - 최대 40자
    $RequestIdentity->reqTitle = '인증요청 메시지 제목란';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $RequestIdentity->expireIn = 1000;
    // 서명 원문 - 최대 2,800자 까지 입력가능
    $RequestIdentity->token = $this->KakaocertService->encrypt('본인인증요청토큰');


    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Talk Message 인증방식
    $RequestIdentity->appUseYN = false;

    // App to App 방식 이용시, 에러시 호출할 URL
    // $RequestIdentity->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestIdentity($clientCode, $RequestIdentity);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestIdentity', ['result' => $result]);
  }

  
  /*
  * 본인인증 요청시 반환된 접수아이디를 통해 서명 상태를 확인합니다.
  */
  public function GetIdentityStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000025';

    try {
      $result = $this->KakaocertService->getIdentityStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetIdentityStatus', ['result' => $result]);
  }

  /*
  * 본인인증 서명을 검증합니다.
  * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
  */
  public function VerifyIdentity(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023030000004';

    // 본인인증 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000025';

    try {
      $result = $this->KakaocertService->verifyIdentity($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifyIdentity', ['result' => $result]);
  }


  /* 
  * 카카오톡 사용자에게 전자서명을 요청합니다.(단건)
  */
  public function RequestSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청정보 객체
    $RequestSign = new RequestSign();

    // 수신자 정보
    // 휴대폰번호,성명,생년월일 또는 Ci(연계정보)값 중 택 일
    $RequestSign->receiverHP = $this->KakaocertService->encrypt('01054437896');
    $RequestSign->receiverName = $this->KakaocertService->encrypt('최상혁');
    $RequestSign->receiverBirthday = $this->KakaocertService->encrypt('19880301');
    // $RequestSign->ci = $KakaocertService->encrypt('');

    // 인증요청 메시지 제목 - 최대 40자
    $RequestSign->reqTitle = '전자서명단건테스트';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $RequestSign->expireIn = 1000;
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $RequestSign->token = $this->KakaocertService->encrypt('전자서명단건테스트데이터');
    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $RequestSign->tokenType = 'TEXT'; // TEXT, HASH

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Talk Message 인증방식
    $RequestSign->appUseYN = false;

    // App to App 방식 이용시, 에러시 호출할 URL
    // $RequestSign->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestSign($clientCode, $RequestSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestSign', ['result' => $result]);
  }

  /*
  * 전자서명 요청에 대한 서명 상태를 확인합니다.
  */
  public function GetSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000024';

    try {
      $result = $this->KakaocertService->getSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetSignStatus', ['result' => $result]);
  }

  /*
  * 전자서명 요청시 반환된 접수아이디를 통해 서명을 검증합니다. (단건)
  * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
  */
  public function VerifySign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000024';

    try {
      $result = $this->KakaocertService->VerifySign($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifySign', ['result' => $result]);
  }

  /*
  * 카카오톡 사용자에게 전자서명을 요청합니다.(복수)
  */
  public function RequestMultiSign(){

     // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청정보 객체
    $RequestMultiSign = new RequestMultiSign();

    // 수신자 정보
    // 휴대폰번호,성명,생년월일 또는 Ci(연계정보)값 중 택 일
    $RequestMultiSign->receiverHP = $this->KakaocertService->encrypt('01054437896');
    $RequestMultiSign->receiverName = $this->KakaocertService->encrypt('최상혁');
    $RequestMultiSign->receiverBirthday = $this->KakaocertService->encrypt('19880301');
    // $RequestMultiSign->ci = $KakaocertService->encrypt('');

      // 인증요청 메시지 제목 - 최대 40자
    $RequestMultiSign->reqTitle = '전자서명단건테스트';
    // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
    $RequestMultiSign->expireIn = 1000;

    // 개별문서 등록 - 최대 20 건
    // 개별 요청 정보 객체
    $RequestMultiSign->tokens = array();
    
    $RequestMultiSign->tokens[] = new MultiSignTokens();
    // 인증요청 메시지 제목 - 최대 40자
    $RequestMultiSign->tokens[0]->reqTitle = "전자서명복수문서테스트1";
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $RequestMultiSign->tokens[0]->token = $this->KakaocertService->encrypt("전자서명복수테스트데이터1");

    $RequestMultiSign->tokens[] = new MultiSignTokens();
    // 인증요청 메시지 제목 - 최대 40자
    $RequestMultiSign->tokens[1]->reqTitle = "전자서명복수문서테스트2";
    // 서명 원문 - 원문 2,800자 까지 입력가능
    $RequestMultiSign->tokens[1]->token = $this->KakaocertService->encrypt("전자서명복수테스트데이터2");

    // 서명 원문 유형
    // TEXT - 일반 텍스트, HASH - HASH 데이터
    $RequestMultiSign->tokenType = 'TEXT'; // TEXT, HASH

    // AppToApp 인증요청 여부
    // true - AppToApp 인증방식, false - Talk Message 인증방식
    $RequestMultiSign->appUseYN = false;

    // App to App 방식 이용시, 에러시 호출할 URL
    // $RequestMultiSign->returnURL = 'https://kakao.barocert.com';

    try {
      $result = $this->KakaocertService->requestMultiSign($clientCode, $RequestMultiSign);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestMultiSign', ['result' => $result]);
  }

  /*
  *  전자서명 요청시 반환된 접수아이디를 통해 서명 상태를 확인합니다. (복수)
  */
  public function GetMultiSignStatus(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000024';

    try {
      $result = $this->KakaocertService->getMultiSignStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetMultiSignStatus', ['result' => $result]);
  }

  

  /*
  * 전자서명 요청시 반환된 접수아이디를 통해 서명을 검증합니다. (복수)
  * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
  */
  public function VerifyMultiSign(){

    // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
    $clientCode = '023030000004';

    // 전자서명 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000018';

    try {
      $result = $this->KakaocertService->verifyMultiSign($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifyMultiSign', ['result' => $result]);
  }


  
  /*
  *  카카오톡 사용자에게 출금동의 전자서명을 요청합니다.
  */
  public function RequestCMS(){

      // 이용기관코드, 파트너가 등록한 이용기관의 코드, (파트너 사이트에서 확인가능)
      $clientCode = '023030000004';

      // 출금동의 요청 정보 객체
      $RequestCMS = new RequestCMS();

      // 수신자 정보
      // 휴대폰번호,성명,생년월일 또는 Ci(연계정보)값 중 택 일
      $RequestCMS->receiverHP = $this->KakaocertService->encrypt('01054437896');
      $RequestCMS->receiverName = $this->KakaocertService->encrypt('최상혁');
      $RequestCMS->receiverBirthday = $this->KakaocertService->encrypt('19880301');
      // $RequestCMS->ci = KakaocertService::encrypt('');;

      // 인증요청 메시지 제목 - 최대 40자
      $RequestCMS->reqTitle = '인증요청 메시지 제공란';
      // 인증요청 만료시간 - 최대 1,000(초)까지 입력 가능
      $RequestCMS->expireIn = 1000;
      // 청구기관명 - 최대 100자
      $RequestCMS->requestCorp = $this->KakaocertService->encrypt('청구 기관명란');
      // 출금은행명 - 최대 100자
      $RequestCMS->bankName = $this->KakaocertService->encrypt('출금은행명란');
      // 출금계좌번호 - 최대 32자
      $RequestCMS->bankAccountNum = $this->KakaocertService->encrypt('9-4324-5117-58');
      // 출금계좌 예금주명 - 최대 100자
      $RequestCMS->bankAccountName = $this->KakaocertService->encrypt('예금주명 입력란');
      // 출금계좌 예금주 생년월일 - 8자
      $RequestCMS->bankAccountBirthday = $this->KakaocertService->encrypt('19880301');
      // 출금유형
      // CMS - 출금동의용, FIRM - 펌뱅킹, GIRO - 지로용
      $RequestCMS->bankServiceType = $this->KakaocertService->encrypt('CMS'); // CMS, FIRM, GIRO

      // AppToApp 인증요청 여부
      // true - AppToApp 인증방식, false - Talk Message 인증방식
      $RequestCMS->appUseYN = false; 

      // App to App 방식 이용시, 에러시 호출할 URL
      // $RequestCMS->returnURL = 'https://kakao.barocert.com';

    try {
        $result = $this->KakaocertService->requestCMS($clientCode, $RequestCMS);
    }
    catch(BarocertException $re) {
        $code = $re->getCode();
        $message = $re->getMessage();
        return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('RequestCMS', ['result' => $result]);
  }

  /*
  * 자동이체 출금동의 요청에 대한 서명 상태를 확인합니다.
  * - 
  */
  public function GetCMSStatus(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023030000004';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000020';

    try {
      $result = $this->KakaocertService->getCMSStatus($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('GetCMSStatus', ['result' => $result]);
  }

  /*
  * 자동이체 출금동의 요청시 반환된 접수아이디를 통해 서명을 검증합니다.
  * 검증하기 API는 완료된 전자서명 요청당 1회만 요청 가능하며, 사용자가 서명을 완료후 유효시간(10분)이내에만 요청가능 합니다.
  */
  public function VerifyCMS(){

    // 이용기관코드, 파트너 사이트에서 확인
    $clientCode = '023030000004';

    // 출금동의 요청시 반환된 접수아이디
    $receiptID = '02304130230300000040000000000020';

    try {
      $result = $this->KakaocertService->verifyCMS($clientCode, $receiptID);
    }
    catch(BarocertException $re) {
      $code = $re->getCode();
      $message = $re->getMessage();
      return view('Response', ['code' => $code, 'message' => $message]);
    }

    return view('VerifyCMS', ['result' => $result]);
  }
}
