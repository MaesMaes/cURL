<?php

/*
 * Нативный парсер работает с HTML документом как со строкой
 */
class parser
{
    // Указатель на начало строки
    private $cur;

    // Строка
    private $str;

    public static function app( $str )
    {
        return new self( $str );
    }

    private function __construct( $str )
    {
        $this->str = $str;
        $this->cur = 0;
    }

    /*
     * Устанавливает указатель на первое вхождение подстроки
     *
     * @param string $str
     */
    public function move_to( $pattern )
    {
        // в строке $this->str ищем $str, начиная с $this->cur
        $res = strpos( $this->str, $pattern, $this->cur );

        // Вхождение не обнаружно
        if( $res === false )
            return -1;

        $this->cur = $res;
        return true;
    }

    /*
     * Ищет первое вхождение подстроки начиная с указателя
     *
     * @param string $str
     */
    function read_to( $pattern )
    {
        // Позиция вхождения подстроки. В строке $this->str ищем $str, начиная с $this->cur
        $res = strpos( $this->str, $pattern, $this->cur );

        // Вхождение не обнаружно
        if( $res === false )
            return -1;

        // В строке $this->str вырезаем подстроку с позиции $this->cur до $res - $this->cur
        $out = substr( $this->str, $this->cur,  $res - $this->cur );
        $this->cur = $res;

        return $out;
    }

    public function move_after( $pattern )
    {

    }

    function read_from( $pattern )
    {

    }

    function subtag(  )
    {

    }

}


