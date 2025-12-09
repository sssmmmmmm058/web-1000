<?php
function force_url_encode(string $str): string {
    $encoded = '';
    $chars = mb_str_split($str, 1, 'UTF-8'); // 支持中文/Emoji/任意字符
    foreach ($chars as $char) {
        $bytes = unpack('C*', $char); // 获取UTF-8字节
        foreach ($bytes as $byte) {
            $encoded .= sprintf('%%%02X', $byte); // 强制编码为%XX（大写十六进制，最终存储格式）
        }
    }
    return $encoded;
}

// 读取环境变量GZCTF_FLAG
$flag = getenv('GZCTF_FLAG') ?: 'No_default_flag_found';

// 全字符强制编码Flag（仅编码一次！作为最终存储和去重的依据）
$force_encoded_flag = force_url_encode($flag);

// 核心：检测是否已存在「值=force_encoded_flag」的Cookie（直接对比，无需解码）
$cookie_value_exists = false;
foreach ($_COOKIE as $existing_cookie_val) {
    // 直接对比原始编码后的值（因为存储时就是这个格式，避免解码导致的编码混乱）
    if ($existing_cookie_val === $force_encoded_flag) {
        $cookie_value_exists = true;
        break; // 找到相同值，停止遍历，不重复设置
    }
}

// 仅当「没有相同Cookie值」时，生成合法随机Cookie名并设置
if (!$cookie_value_exists) {
    // 合法字符集：符合Cookie规范（无禁止字符，避免setcookie报错）
    $start_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_-'; // 第1位非数字（兼容所有浏览器）
    $all_chars = $start_chars . '0123456789'; // 全部合法字符（字母+数字+下划线+连字符）
    
    // 生成16位随机合法Cookie名
    $cookie_name = $start_chars[random_int(0, strlen($start_chars) - 1)]; // 第1位：非数字
    $all_chars_len = strlen($all_chars);
    for ($i = 1; $i < 16; $i++) { // 第2-16位：随机选取
        $cookie_name .= $all_chars[random_int(0, $all_chars_len - 1)];
    }

    // 设置Cookie（HttpOnly+1小时有效期+无双重编码）
    setcookie(
        $cookie_name,
        $force_encoded_flag, // 直接存储force_url_encode的结果（仅编码一次）
        time() + 3600,
        '/', // 全站可用
        '', // 默认当前域名
        false, // 开发环境兼容HTTP；生产环境建议改为true
        true // HttpOnly：禁止JS读取，防XSS攻击
    );
}

header('Content-Type: text/plain; charset=utf-8');
echo '🍪';
exit;
?>