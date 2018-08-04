<?php
session_start();

	// POST 값
	$name = htmlspecialchars($_POST['name']);
	$email = htmlspecialchars($_POST['email']);
	$phone = htmlspecialchars($_POST['phone']);
	
	// 비어있는지 확인
	if(empty($name) || empty($email) || empty($phone)){
		echo "<script>alert('빈칸을 모두 입력해주세요.');</script>";
		return false;
	}
	
	// 이메일 유효성 확인
	$check_email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if($check_email == false){
		echo "<script>alert('이메일이 유효하지 않습니다.');</script>";
		return false;
	}
	
	// 이름 글자 제한
	if(mb_strlen($name) > 25){
		echo "<script>alert('유튜브 닉네임은 25글자 이하로 입력하세요.');</script>";
		return false;
	}
	
	// 휴대폰 번호 체크
	// 숫자 확인
	if(!is_numeric($phone)){
		echo "<script>alert('전화번호는 숫자로 입력 해주세요.');</script>";
		return false;
	}
	
	// 11개의 글자인지 확인
	if(strlen($phone) != 11){
		echo "<script>alert('전화번호가 11자리인지 확인 해주세요.');</script>";
		return false;
	}
	
	// 010 으로 시작하는지 확인
	if(substr($phone, 0, 3) != "010"){
		echo "<script>alert('전화번호는 010 으로 시작해야 합니다.');</script>";
		return false;
	}
	
	// 세션에 저장
	$_SESSION["name"] = $name;
	$_SESSION["email"] = $email;
	$_SESSION["phone"] = $phone;
	
	echo("<script>location.href='/youtube/login.php';</script>");

?>