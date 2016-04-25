<?php

/**
 * CURL HTTP���󹤾ߣ�
 * ֧�����¹��ܣ�
 * 1��֧��ssl���Ӻ�proxy��������
 * 2: ��cookie���Զ�֧��
 * 3: �򵥵�GET/POST�������
 * 4: ֧�ֵ����ļ��ϴ���ͬ�ֶεĶ��ļ��ϴ�,֧�����·������·��.
 * 5: ֧�ַ��ط�������ǰ����������еķ�������Ϣ�ͷ�����Header��Ϣ
 * 6: �Զ�֧��lighttpd������
 * 7: ֧���Զ����� REFERER ����ҳ
 * 8: �Զ�֧�ַ�����301��ת����д����(лл֣GG)
 * 9: �����ѡ��,���Զ���˿ڣ���ʱʱ�䣬USERAGENT��Gzipѹ����.
 */
class HTTP_CURL {

    //CURL���
    private $ch = null;
    //CURLִ��ǰ�������û�������˷��ص���Ϣ
    private $info = array();
    //CURL SETOPT ��Ϣ
    private $setopt = array(
        //���ʵĶ˿�,httpĬ���� 80
        'port' => 80,
        //�ͻ��� USERAGENT,��:"Mozilla/4.0",Ϊ����ʹ���û��������
        'userAgent' => '',
        //���ӳ�ʱʱ��
        'timeOut' => 30,
        //�Ƿ�ʹ�� COOKIE ����򿪣���Ϊһ����վ�����õ�
        'useCookie' => true,
        //�Ƿ�֧��SSL
        'ssl' => false,
        //�ͻ����Ƿ�֧�� gzipѹ��
        'gzip' => true,

        //�Ƿ�ʹ�ô���
        'proxy' => false,
        //��������,��ѡ�� HTTP �� SOCKS5
        'proxyType' => 'HTTP',
        //����������ַ,����� HTTP ��ʽ��Ҫд��URL��ʽ��:"http://www.proxy.com"
        //SOCKS5 ��ʽ��ֱ��д��������ΪIP����ʽ����:"192.168.1.1"
        'proxyHost' => 'http://www.proxy.com',
        //��������Ķ˿�
        'proxyPort' => 1234,
        //�����Ƿ�Ҫ�����֤(HTTP��ʽʱ)
        'proxyAuth' => false,
        //��֤�ķ�ʽ.��ѡ�� BASIC �� NTLM ��ʽ
        'proxyAuthType' => 'BASIC',
        //��֤���û��������
        'proxyAuthUser' => 'user',
        'proxyAuthPwd' => 'password',
    );
    private $header = array(
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3",
        //"Accept-Encoding: gzip,deflate,sdch",
        "Accept-Language: zh-CN,zh;q=0.8",
        "Cache-Control: max-age=0",
        "Connection: keep-alive",
    );

    /**
     * ���캯��
     *
     * @param array $setopt :��ο� private $setopt ������
     */
    public function __construct($setopt = array()) {


        //�ϲ��û������ú�ϵͳ��Ĭ������
        $this->setopt = array_merge($this->setopt, $setopt);
        //���û�а�װCURL����ֹ����
        function_exists('curl_init') || die('CURL Library Not Loaded');
        //��ʼ��
        $this->ch = curl_init();
        //����CURL���ӵĶ˿�
        //curl_setopt($this->ch, CURLOPT_PORT, $this->setopt['port']);
        //ʹ�ô���
        if ($this->setopt['proxy']) {
            $proxyType = $this->setopt['proxyType'] == 'HTTP' ? CURLPROXY_HTTP : CURLPROXY_SOCKS5;
            curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxyType);
            curl_setopt($this->ch, CURLOPT_PROXY, $this->setopt['proxyHost']);
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $this->setopt['proxyPort']);
            //����Ҫ��֤
            if ($this->setopt['proxyAuth']) {
                $proxyAuthType = $this->setopt['proxyAuthType'] == 'BASIC' ? CURLAUTH_BASIC : CURLAUTH_NTLM;
                curl_setopt($this->ch, CURLOPT_PROXYAUTH, $proxyAuthType);
                $user = "[{$this->setopt['proxyAuthUser']}]:[{$this->setopt['proxyAuthPwd']}]";
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $user);
            }
        }
        //����ʱ�Ὣ���������������صġ�Location:������header�еݹ�ķ��ظ������
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        //�򿪵�֧��SSL
        if ($this->setopt['ssl']) {
            //curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            //curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1); // return into a variable 
            //������֤֤����Դ�ļ��
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            //��֤���м��SSL�����㷨�Ƿ����
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, true);
        }

        //����httpͷ,֧��lighttpd�������ķ���
        $header[] = 'Expect:';
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        //���� HTTP USERAGENT
        $userAgents = array(
            'chrome' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1',
            'firefox' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB6; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; OfficeLiveConnector.1.4; OfficeLivePatch.1.3)',
        );
        $userAgent = $this->setopt['userAgent'] ? $this->setopt['userAgent'] : $_SERVER['HTTP_USER_AGENT'];
        if (!$userAgent) {
            $userAgent = $userAgents[array_rand($userAgents)];
        }
        curl_setopt($this->ch, CURLOPT_USERAGENT, $userAgent);
        //�������ӵȴ�ʱ��,0���ȴ�
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->setopt['timeOut']);
        //����curl����ִ�е������
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->setopt['timeOut']);
        //���ÿͻ����Ƿ�֧�� gzipѹ��
        if ($this->setopt['gzip']) {
            curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
        }
        //�Ƿ�ʹ�õ�COOKIE
        if ($this->setopt['useCookie']) {
            //��ɴ����ʱCOOKIE���ļ�(Ҫ���·��)
            $cookfile = tempnam(sys_GET_temp_dir(), 'cuk');
            //���ӹر��Ժ󣬴��cookie��Ϣ
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookfile);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookfile);
        }
        //�Ƿ�ͷ�ļ�����Ϣ��Ϊ��������(HEADER��Ϣ),���ﱣ������
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        //��ȡ����Ϣ���ļ�������ʽ���أ�����ֱ�������
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true);
    }

    public function setHeader($key, $value) {

        $this->unsetHeader($key);

        $this->header[] = "$key: $value";
    }

    public function unsetHeader($key) {

        foreach ($this->header as $header_key => $header_value) {
            if (strpos(strtolower($header_value), strtolower($key)) === 0) {
                unset($this->header[$header_key]);
            }
        }
    }


    /**
     * �� GET ��ʽִ������
     *
     * @param string $url :�����URL
     * @param array $params ������Ĳ���,��ʽ��: array('id'=>10,'name'=>'yuanwei')
     * @param array $referer :����ҳ��,Ϊ��ʱ�Զ�����,���������ж�������ƵĻ���һ��Ҫ���õ�.
     *
     * @return ���󷵻�:false ��ȷ����:�������
     */
    public function get($url, $host = '', $params = array(), $referer = '') {

        if ($host) {
            $this->setHeader('Host', $host);
        }

        return $this->_request('GET', $url, $params, array(), $referer);
    }

    /**
     * �� POST ��ʽִ������
     *
     * @param string $url :�����URL
     * @param array $params ������Ĳ���,��ʽ��: array('id'=>10,'name'=>'yuanwei')
     * @param array $uploadFile :�ϴ����ļ�,֧�����·��,��ʽ����
     * �����ļ��ϴ�:array('img1'=>'./file/a.jpg')
     * ͬ�ֶζ���ļ��ϴ�:array('img'=>array('./file/a.jpg','./file/b.jpg'))
     * @param array $referer :����ҳ��,����ҳ��,Ϊ��ʱ�Զ�����,���������ж�������ƵĻ���һ��Ҫ���õ�.
     *
     * @return ���󷵻�:false ��ȷ����:�������
     */
    public function post($url, $params = array(), $uploadFile = array(), $referer = '') {
        return $this->_request('POST', $url, $params, $uploadFile, $referer);
    }


    /**
     * �õ�������Ϣ
     *
     * @return string
     */
    public function getError() {

        return curl_error($this->ch);
    }

    /**
     * �õ��������
     *
     * @return int
     */
    public function getErrCode() {

        return curl_errno($this->ch);
    }

    /**
     * �õ���������ǰ����������еķ�������Ϣ�ͷ�����Header��Ϣ,����
     * [before] ������ǰ�����õ���Ϣ
     * [after] :��������еķ�������Ϣ
     * [header] :������Header������Ϣ
     *
     * @return array
     */
    public function getHeader() {

        return $this->info;
    }


    /**
     * ��������
     *
     */
    public function __destruct() {

        //�ر�CURL
        curl_close($this->ch);
    }

    /**
     * ˽�з���:ִ����������
     *
     * @param string $method :HTTP����ʽ
     * @param string $url :�����URL
     * @param array $params ������Ĳ���
     * @param array $uploadFile :�ϴ����ļ�(ֻ��POSTʱ����Ч)
     * @param array $referer :����ҳ��
     *
     * @return ���󷵻�:false ��ȷ����:�������
     */
    private function _request($method, $url, $params = array(), $uploadFile = array(), $referer = '') {
        //�������GET��ʽ������Ҫ���ӵ�URL����
        if ($method == 'GET') {
            $url = $this->_parseUrl($url, $params);
        }
        //���������URL
        curl_setopt($this->ch, CURLOPT_URL, $url);

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->header);
        //����������ҳ,�����Զ�����
        if ($referer) {
            curl_setopt($this->ch, CURLOPT_REFERER, $referer);
        } else {
            curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        }
        curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.3 Safari/537.36");

        $urlInfo = parse_url($url);
        $cookieJar = "/tmp/cookie_{$urlInfo['host']}";
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookieJar);

        //�����POST
        if ($method == 'POST') {
            //����һ�������POST��������Ϊ��application/x-www-form-urlencoded
            curl_setopt($this->ch, CURLOPT_POST, true);
            //����POST�ֶ�ֵ
            $postData = $this->_parsmEncode($params, false);
            //������ϴ��ļ�
            if ($uploadFile) {
                foreach ($uploadFile as $key => $file) {
                    if (is_array($file)) {
                        $n = 0;
                        foreach ($file as $f) {
                            //�ļ������Ǿ��·��
                            $postData[$key . '[' . $n++ . ']'] = '@' . realpath($f);
                        }
                    } else {
                        $postData[$key] = '@' . realpath($file);
                    }
                }
            }
            //pr($postData); die;
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);
        }

        //�õ��������õ���Ϣ
        $this->info['before'] = curl_GETinfo($this->ch);
        //��ʼִ������
        $result = curl_exec($this->ch);
        //�õ�����ͷ
        $headerSize = curl_GETinfo($this->ch, CURLINFO_HEADER_SIZE);
        $this->info['header'] = substr($result, 0, $headerSize);
        //ȥ������ͷ
        $result = substr($result, $headerSize);
        //�õ����а������������ص���Ϣ
        $this->info['after'] = curl_GETinfo($this->ch);
        //�������ɹ�
        if ($this->getErrCode() == 0) { //&& $this->info['after']['http_code'] == 200
            return $result;
        } else {
            return false;
        }

    }

    /**
     * ���ؽ������URL��GET��ʽʱ���õ�
     *
     * @param string $url :URL
     * @param array $params :����URL��Ĳ���
     *
     * @return string
     */
    private function _parseUrl($url, $params) {
        $fieldStr = $this->_parsmEncode($params);
        if ($fieldStr) {
            $url .= strstr($url, '?') === false ? '?' : '&';
            $url .= $fieldStr;
        }
        return $url;
    }

    /**
     * �Բ������ENCODE����
     *
     * @param array $params :����
     * @param bool $isRetStr : true�����ַ��� false:�����鷵��
     *
     * @return string || array
     */
    private function _parsmEncode($params, $isRetStr = true) {
        $fieldStr = '';
        $spr = '';
        $result = array();
        foreach ($params as $key => $value) {
            $value = urlencode($value);
            $fieldStr .= $spr . $key . '=' . $value;
            $spr = '&';
            $result[$key] = $value;
        }
        return $isRetStr ? $fieldStr : $result;
    }
}
