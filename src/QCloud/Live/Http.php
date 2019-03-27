<?php
namespace QCloud\Live;


class Http
{
    public static function get($url, $body)
    {
		$ch = curl_init();
        if (is_array($body)) {
            $symbol = strstr($url, "?") ? "&" : "?";
            $url = $body == NULL ? $url : $url . $symbol . http_build_query($body);
        } else {
            $url = $url . '?' . $body;
        }
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 100000);//超时时间
        $ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
    }


    /**
     * 执行网络请求
     *
     * @param array $options
     * @return mixed
     * @throws BaiduException
     */
    protected function post(array $options)
    {

        // 特殊处理
        $client = new Client('vop.baidu.com');
        $client->set(
            [
                'timeout'            => 10, // 请求超时时间(10秒)
                'buffer_output_size' => 2097152,
                'package_max_length' => 2097152,
                'socket_buffer_size' => 2097152, //2M缓存区
            ]
        );
        $client->setHeaders(
            [
                'Content-Type' => 'application/json'
            ]
        );
        $client->post('/server_api', json_encode($options));
        $client->close();

        // 提取结果
        $result = json_decode($client->body, true);

        // 获取结果失败
        if (!is_array($result)) {
            throw new BaiduException('获取音频转换结果失败：statusCode - ' . $client->statusCode . ' | body - ' . $client->body . ' | errorMsg - ' . $client->errMsg, 500900);
        }
        // 转换出错
        if ($result['err_no'] > 0) {
            throw new BaiduException('语音识别失败，请重试：' . $result['err_no'] . ' - ' . $result['err_msg'], 500900);
        }

        return $result['result'];
    }
}