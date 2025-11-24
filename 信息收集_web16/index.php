<?php

define('TIMEOUT', 5);

// 一言类型映射表
$hitokotoTypeMap = [
    'a' => '动画', 'b' => '漫画', 'c' => '游戏', 'd' => '文学',
    'e' => '原创', 'f' => '来自网络', 'g' => '其他', 'h' => '影视',
    'i' => '诗词', 'j' => '网易云', 'k' => '哲学', 'l' => '抖机灵'
];

/**
 * 后端代理请求图片接口
 */
function proxyGetImage() {
    $apiUrl = "https://rdimg.yumehinata.com/random-wallpaper";
    $ch = curl_init($apiUrl);

    // 复刻curl请求头
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true, // 跟随307重定向
        CURLOPT_TIMEOUT => TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Encoding: gzip, deflate, br, zstd',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Cache-Control: max-age=0',
            'Dnt: 1',
            'Priority: u=0, i',
            'Sec-Ch-Ua: "Chromium";v="142", "Microsoft Edge";v="142", "Not A Brand";v="99"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: cross-site',
            'Sec-Fetch-User: ?1',
            'Upgrade-Insecure-Requests: 1'
        ]
    ]);

    $response = curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // 获取307重定向后的最终URL
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'final_url' => $finalUrl,
        'error' => $httpCode >= 400 ? "请求失败，状态码: {$httpCode}" : ''
    ];
}

/**
 * 一言逻辑：时间戳转换
 */
function getRandomHitokoto() {
    global $hitokotoTypeMap;
    $ch = curl_init('https://v1.hitokoto.cn/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    if (empty($response)) {
        return ['error' => "一言接口请求失败"];
    }

    $hitokoto = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($hitokoto['hitokoto'])) {
        return ['error' => "一言接口返回无效数据"];
    }

    // 类型映射
    $hitokoto['type_name'] = $hitokotoTypeMap[$hitokoto['type']] ?? '动画';
    // 时间戳转换
    $createdAt = is_numeric($hitokoto['created_at']) 
        ? (int)$hitokoto['created_at'] 
        : strtotime($hitokoto['created_at']);
    $hitokoto['display_time'] = ($createdAt > 0) 
        ? date('Y-m-d H:i:s', $createdAt) 
        : $hitokoto['created_at'];

    return $hitokoto;
}

// 执行后端代理请求
$imageData = proxyGetImage();
// 获取一言数据
$hitokotoData = getRandomHitokoto();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Microsoft YaHei", serif; }
        body { background: #f5f5f5; color: #333; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); padding: 30px; }
        
        /* 图片区域：后端代理无限制 */
        #image-container { 
            max-width: 100%; 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
            cursor: pointer;
            display: inline-block;
            position: relative;
            transition: box-shadow 0.3s;
        }
        #image-container:hover { box-shadow: 0 6px 25px rgba(0,0,0,0.15); }
        #wallpaper { 
            max-width: 100%; 
            height: auto; 
            display: block; 
        }
        /* 调试信息（显示PID+原文件名） */
        .debug-info {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 5;
            max-width: 80%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* 一言区域：简化样式 */
        .hitokoto-section {
            padding: 20px 0;
            margin: 30px auto 0;
            max-width: 800px;
        }
        /* 一言内容 */
        .hitokoto-content {
            font-size: 20px;
            line-height: 2.2;
            font-style: italic;
            color: #2d3748;
            max-width: 100%;
            word-wrap: break-word;
            word-break: break-all;
        }
        /* 发言人：两个tab缩进 */
        .hitokoto-author {
            font-size: 18px;
            color: #4a5568;
            margin-top: 10px;
            margin-left: 2em;
            display: block;
        }
        
        .error { color: #dc2626; background: #fef2f2; padding: 15px; border-radius: 8px; margin: 10px 0; text-align: center; }
        
        @media (max-width: 768px) {
            .hitokoto-content { font-size: 17px; line-height: 2; }
            .hitokoto-author { font-size: 16px; margin-left: 1.5em; }
            .container { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 图片区域：后端代理加载 -->
        <div class="image-section" style="text-align: center;">
            <div id="image-container">
                <?php if ($imageData['success']): ?>
                    <!-- 直接使用后端获取的最终URL（保留原文件名） -->
                    <img 
                        id="wallpaper"
                        src="<?php echo htmlspecialchars($imageData['final_url']); ?>" 
                        alt="随机图片：<?php echo htmlspecialchars(basename($imageData['final_url'])); ?>" 
                        loading="lazy">
                    
                    <!-- 解析PID并显示 -->
                    <?php 
                    $finalUrl = $imageData['final_url'];
                    $filename = basename($finalUrl);
                    $pid = explode('_', $filename)[0];
                    $pidValid = is_numeric($pid);
                    ?>
                    <div class="debug-info">
                        <!--<?php echo $pidValid ? "PID: {$pid} | 文件名: {$filename}" : "文件名: {$filename} | 无PID"; ?>-->
                        <?php echo $pidValid ? "PID: {$pid}" : "无PID"; ?>
                    </div>
                <?php else: ?>
                    <div class="error"><?php echo htmlspecialchars($imageData['error']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 一言区域：简化样式 -->
        <div class="hitokoto-section" 
             title="类型：<?php echo htmlspecialchars($hitokotoData['type_name'] ?? '未知'); ?>
    出处：<?php echo htmlspecialchars($hitokotoData['from'] ?? '未知'); ?>
    添加时间：<?php echo htmlspecialchars($hitokotoData['display_time'] ?? '未知'); ?>">
            <?php if (isset($hitokotoData['error'])): ?>
                <div class="error"><?php echo htmlspecialchars($hitokotoData['error']); ?></div>
            <?php else: ?>
                <div class="hitokoto-content"><?php echo htmlspecialchars($hitokotoData['hitokoto']); ?></div>
                <div class="hitokoto-author">——<?php echo htmlspecialchars($hitokotoData['from_who'] ?? '未知作者'); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // 点击图片跳转（后端获取的最终URL）
        document.getElementById('image-container').addEventListener('click', function() {
            const imgUrl = document.getElementById('wallpaper')?.src;
            if (imgUrl) {
                window.open(imgUrl, '_blank', 'noopener noreferrer');
            }
        });
    </script>
</body>
</html>