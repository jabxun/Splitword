<?php
namespace Split\Constants;

class Dict
{
    /**
     * 字典的索引掩码.
     */
    const WORD_INDEX_MASK = 0XFFFF;

    /**
     * 前置词.
     */
    const WORD_BEFORE = 1;
    /**
     * 后缀词.
     */
    const WORD_ENDED = 2;
    /**
     * 停止词.
     */
    const WORD_STOP = 3;

    /**
     * 字典操作模式-读取
     */
    const MODE_READ = 'r';
    /**
     * 字典操作模式-写入.
     */
    const MODE_WRITE = 'w';

    /**
     * 词类型-标点.
     */
    const WORD_TYPE_PU = 1;
    /**
     * 词类型-数字.
     */
    const WORD_TYPE_NU = 2;
    /**
     * 词类型-英文.
     */
    const WORD_TYPE_EN = 3;
    /**
     * 词类型-不可见字符.
     */
    const WORD_TYPE_NO = 4;
    /**
     * 词类型-中文或其他文字.
     */
    const WORD_TYPE_CN = 5;

    /**
     * ascii字符开始.
     */
    const WORD_EN_START = 0X0;
    /**
     * ascii字符结束.
     */
    const WORD_EN_ENDED = 0X80;
    /**
     * ascii不可见字符结束-开始同ascii字符开始.
     */
    const WORD_EN_NO_ENDED = 0X20;
    /**
     * ascii数字开始.
     */
    const WORD_EN_NU_START = 0X2F;
    /**
     * ascii数字结束.
     */
    const WORD_EN_NU_ENDED = 0X3A;
    /**
     * ascii大写字母开始.
     */
    const WORD_EN_UP_START = 0X40;
    /**
     * ascii大写字母结束.
     */
    const WORD_EN_UP_ENDED = 0X5B;
    /**
     * ascii小写字母开始.
     */
    const WORD_EN_LOW_START = 0X60;
    /**
     * ascii小写字母结束.
     */
    const WORD_EN_LOW_ENDED = 0X7B;

    /**
     * 字典二进制索引长度-字节.
     */
    const BINARY_INDEX_LENGTH = 0X8;
    /**
     * 字典索引左移长度.
     */
    const BINARY_INDEX_OFFSET = 0X3;

    const BINARY_MAX_OFFSET = OX400;
}
