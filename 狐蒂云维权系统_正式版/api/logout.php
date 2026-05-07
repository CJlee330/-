<?php
/**
 * 用户登出API
 */

header('Content-Type: application/json; charset=utf-8');

session_start();
session_destroy();

successResponse('已退出登录');
