<?php

/*
 * Barocert API PHP SDK Laravel Example
 *
 * 업데이트 일자 : 2024-04-16
 * 연동기술지원 연락처 : 1600-9854
 * 연동기술지원 이메일 : code@linkhubcorp.com
 *         
 * <테스트 연동개발 준비사항>
 *   1) API Key 변경 (연동신청 시 메일로 전달된 정보)
 *       - LinkID : 링크허브에서 발급한 링크아이디
 *       - SecretKey : 링크허브에서 발급한 비밀키
 *   2) SDK 환경설정 필수 옵션 설정
 *       - IPRestrictOnOff : 인증토큰 IP 검증 설정, true-사용, false-미사용, (기본값:true)
 *       - UseStaticIP : 통신 IP 고정, true-사용, false-미사용, (기본값:false)
 *
 * This module uses curl and openssl for HTTPS Request. So related modules must
 * be installed and enabled.
 */

return [

    // 링크아이디
    'LinkID' => 'TESTER',

    // 비밀키
    'SecretKey' => 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=',

    // 통신방식 기본은 CURL , PHP curl 모듈 사용에 문제가 있을 경우 STREAM 기재가능.
    // STREAM 사용시에는 php.ini의 allow_url_fopen = on 으로 설정해야함.
    'LINKHUB_COMM_MODE' => 'CURL',

    // 인증토큰 IP 검증 설정, true-사용, false-미사용, (기본값:true)
    'IPRestrictOnOff' => true,

    // 통신 IP 고정, true-사용, false-미사용, (기본값:false)
    'UseStaticIP' => false
];
