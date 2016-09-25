<?php
namespace Split\Stream;

/**
 * 流操作基础类.
 * 
 * @author jabxun <jabxun@163.com>
 * @version 1.0
 */
class Stream
{
    protected $bufferSize = 4096;
    protected $stream;
    protected $isOpen;

    /*
     * @var array 读取模式.
     */
    protected static $readableModes = [
        'r', 'r+', 'w+', 'a+', 'x+', 'c+', 'rb', 'r+b', 'w+b', 'a+b', 'x+b', 'c+b', 'rt', 'r+t', 'w+t', 'a+t', 'x+t', 'c+t'
    ];
    /**
     * @var array 写入模式.
     */
    protected static $writableModes = array(
    'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+', 'r+b', 'wb', 'w+b', 'ab', 'a+b', 'xb', 'x+b', 'cb', 'c+b', 'r+t', 'wt', 'w+t', 'at', 'a+t', 'xt', 'x+t', 'ct', 'c+t'
    );

    /**
     * Stream constructor.
     * @param resource $stream
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
        $this->isOpen = true;
    }

    /**
     * 获取资源.
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->stream;
    }

    /**
     * 获取流的meta元素数组.
     *
     * @return array
     */
    public function getMetadata()
    {
        return stream_get_meta_data($this->stream);
    }

    /**
     * 获取meta指定项信息.
     * 
     * @param string $key 指定项
     * @return null|string
     */
    public function getMetadataForKey($key)
    {
        $metadata = $this->getMetadata();
        if (isset($metadata[$key])) {
            return $metadata[$key];
        }
    }

    /**
     * 判断是否是本地流.
     *
     * @return Boolean
     */
    public function isLocal()
    {
        return stream_is_local($this->stream);
    }

    /**
     * 是否可读.
     * 
     * @return Boolean
     */
    public function isReadable()
    {
        return in_array($this->getMetadataForKey('mode'), self::$readableModes);
    }

    /**
     * 是否可写入.
     * 
     * @return Boolean
     */
    public function isWritable()
    {
        return in_array($this->getMetadataForKey('mode'), self::$writableModes);
    }

    /**
     * 是否可以一定指针.
     * 
     * @return Boolean
     */
    public function isSeekable()
    {
        return $this->getMetadataForKey('seekable');
    }

    /**
     * 判断流是否已经打开.
     * 
     * @return Boolean
     */
    public function isOpen()
    {
        return $this->isOpen;
    }
    /**
     * 获取缓冲区大小
     * 
     * @param int 字节
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * 获取缓冲区大小
     *
     * @return int 字节
     */
    public function getBufferSize()
    {
        return $this->bufferSize;
    }

    /**
     * 读取数据.
     *
     * @param int $length 数据长度
     *
     * @return string
     */
    public function read($length = null)
    {
        if (null == $length) {
            $length = $this->bufferSize;
        }
        $ret = fread($this->stream, $length);

        return $ret;
    }
    /**
     * 读取行.
     *
     * @param int $length 字符串长度
     * @param string $ending 换行标识
     *
     * @return string
     */
    public function getLine($length = null, $ending = "\n")
    {
        if (null == $length) {
            $length = $this->bufferSize;
        }
        $ret = stream_get_line($this->stream, $length, $ending);

        return $ret;
    }

    /**
     * 获取内容.
     *
     * @return string The data read from the stream
     */
    public function getContent()
    {
        return stream_get_contents($this->stream);
    }

    /**
     * 判断是否到流末尾.
     *
     * @return Boolean
     */
    public function isEOF()
    {
        return feof($this->stream);
    }

    /**
     * 写操作.
     *
     * @param string $string 写入字符串
     * @param int $length 写入长度
     *
     * @return int 写字符数量
     */
    public function write($string, $length = null)
    {
        if (null === $length) {
            $ret = fwrite($this->stream, $string);
        } else {
            $ret = fwrite($this->stream, $string, $length);
        }

        return $ret;
    }

    /**
     * 从一个流移动到另外一个流.
     *
     * @param Stream $stream 目标流
     *
     * @return int 移动的数据量
     */
    public function pipe(Stream $stream)
    {
        return stream_copy_to_stream($this->getResource(), $stream->getResource());
    }

    /**
     * 获取偏移量.
     * 
     * @return int
     */
    public function getOffset()
    {
        $ret = ftell($this->stream);

        return $ret;
    }

    /**
     * 移动指针偏移量
     *
     * @param int $offset 偏移量
     * @param int $whence 从哪里开始 - [SEEK_SET:起始位置, SEEK_CUR:现在位置, SEEK_END:终止位置]
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->stream, $offset, $whence);
    }

    /**
     * 重置文件指针.
     */
    public function rewind()
    {
        rewind($this->stream);
    }

    /**
     * 
     */
    public function close()
    {
        fclose($this->stream);
    }
    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
