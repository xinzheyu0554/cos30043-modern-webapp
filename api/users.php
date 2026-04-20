<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// 临时测试数据（绕过数据库）
echo json_encode([
    "status" => "success",
    "data" => [
        [
            "userId" => 1,
            "firstName" => "Test",
            "lastName" => "User",
            "email" => "test@example.com",
            "role" => "admin",
            "address" => "Melbourne",
            "phoneNumber" => "123456789",
            "isActive" => 1,
            "createdAt" => "2026-01-01",
            "updatedAt" => null
        ]
    ]
]);