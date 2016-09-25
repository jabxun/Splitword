<?php
namespace Split\Constants;

class Character
{
    /**
     * 输入转码字符集.
     */
    const CHARSET_IN = 'ucs-2le';
    /**
     * 输出转码字符集.
     */
    const CHARSET_OUT = 'ucs-2be';

    /**
     * 支持的字符集-utf8.
     */
    const SUPPORT_CHARSET_UTF8 = 'utf-8';
    /**
     * 支持的字符集-big5.
     */
    const SUPPORT_CHARSET_BIG5 = 'big5';
    /**
     * 支持的字符集-gbk.
     */
    const SUPPORT_CHARSET_GBK = 'gb18030';
}
