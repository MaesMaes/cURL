<?php

/**
 * Будем строить класс по спецификации HTML 1.1 keep a live.
 * На 1 домен ставим 1 соединение. После чего получаем информацию
 * реквестами.
 *
 * Методы:
 * set( $name, $value )     - Устанавливает опцию курла и записывает в массив с опциями
 * get( $name )             - Отображает текущее состояние опции
 * config_save( $file )     - Сохранить конфигурацию в файл
 * config_load( $file )     - Загрузить конфигурацию из файла
 * ----------------------------------------------------------------
 * ssl( $act )              - Включает или выключает возможность обращаться к HTTPS страницам
 * redirect( $act )         - Устанавливает, следовать ли за перенаправлением
 * headers( $act )          - Включает или выключает заголовки ответа
 * referer( $url )          - Устанавливает реферер
 * agent( $agent )          - Устанавливает браузер
 * ----------------------------------------------------------------
 * request( $url )          - Запросы на страницы в пределах домена
 * add_header( $header )    - Добавить 1 произвольный http-заголовок к запросу
 * add_headers( $headers )  - Добавить несколько произвольных http-заголовоков к запросу
 * clear_headers()          - Очиситить массив произвольных http-заголовков
 * cookie( $cookie )        - Устанавливает настройки куков
 * post( $post )            - Отправка POST запроса ассоциативного массива
 */
class curl {

    // экземпляр cURL
    private $ch;
    // хост
    private $host;
    // Конфигарация настроек cURL
    private $options;

    /**
     * Инициализация класса для конкретного домена
     */
     public static function app( $host )
     {
         return new self( $host );
     }

     // Конструктор
     private function __construct( $host )
     {
         $this->host = $host;
         $this->ch = curl_init();

         // Настройки соединения по умолчанию
         $this->options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => array());
 	     curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
     }

     // Деструктор закрывает соединение
     public function __destruct()
     {
         curl_close( $this->ch );
     }

     /**
       * Устанавливает опцию курла и записывает в массив с опциями
       *
       * @param mixed $name
       * Константа (название) или номер опции курла
       *
       * @param mixeds $value
       * Значение опции для установки
      */
     public function set( $name, $value )
     {
         curl_setopt( $this->ch, $name, $value );

         // Сохраняем параметр в общей конфигурации
         $this->options[$name] = $value;

         // для цепных вызовов
         return $this;
     }

     /**
      * Отображает текущее состояние опции
      *
      * @param mixed $name
      * Константа (название) или номер опции курла
      */
      public function get( $name )
      {
         return $this->options[$name];
      }

     /**
      * Сохранить конфигурацию в файл
      *
      * @param string $file
      */
     public function config_save( $file )
     {
        if( !strpos( $file, '.' ) ) $file .= '.config';

        $data = serialize( $this->options );
        file_put_contents( 'lib/config/' . $file, $data );
        return $this;
     }

     /**
      * Загрузить конфигурацию из файла
      *
      * @param string $file
      */
     public function config_load( $file )
     {
        if( !strpos( $file, '.' ) ) $file .= '.config';

        $data = file_get_contents( 'lib/config/' . $file );
        $data = unserialize( $data );

        // Установить настройки для всего cURL
        curl_setopt_array( $this->ch, $data );

        // Синхронизируем конфигурацию с настройкой класса
        foreach ($data as $key => $value)
        {
            $this->options[$key] = $value;
        }

        return $this;
     }

     /**
     * Включает или выключает возможность обращаться к HTTPS страницам
     *
     * @param int $act
     * 1 - https разрешено, 0 - https запрещено
     */
     public function ssl( $act )
     {
         // Остановки cURL от проверки сертификата узла сети.
         $this->set( CURLOPT_SSL_VERIFYPEER, $act );
         $this->set( CURLOPT_SSL_VERIFYHOST, $act );
         return $this;
     }

     /**
      * Устанавливает, следовать ли за перенаправлением
      *
      * @param bool $param
      * TRUE - следовать
      * FALSE - не следовать
      */
     public function redirect( $act )
     {
         $this->set( CURLOPT_FOLLOWLOCATION, $act );
         return $this;
     }

     /**
      * Включает или выключает заголовки ответа
      *
      * @param int $act
      * 1 - есть, 0 - нет
      */
 	public function headers( $act )
    {
 		$this->set(CURLOPT_HEADER, $act);
 		return $this;
 	}

     /**
      * Запросы на страницы в пределах домена
      */
     public function request( $url )
     {
         curl_setopt( $this->ch, CURLOPT_URL, $this->make_url( $url ) );
         $data = curl_exec( $this->ch );
         return $this->process_result( $data );
         //return $data;
     }

     // Разрешаем проблему слешей в адресе
     private function make_url( $url )
     {
        if ( $url[0] != '/' )
            $url = '/' . $url;
        return $this->host . $url;
     }

     // Разделение ответа сервера от заголовков
     private function process_result( $data )
     {
         /* Если HEADER отключен */
 		if( !isset($this->options[CURLOPT_HEADER]) || !$this->options[CURLOPT_HEADER] ) {
 			return array(
 				'headers' => array(),
 				'html' => $data
 			);
 		}

 		/* Разделяем ответ на headers и body */
 		$info = curl_getinfo( $this->ch );

        // trim - чтобы обрезать перенос строки в конце
        $headers_part = trim( substr($data, 0, $info['header_size']) );
 		$body_part = substr( $data, $info['header_size'] );

 		/* Определяем символ переноса строки */
        // винда в никсовую
 		$headers_part = str_replace( "\r\n", "\n", $headers_part );
        // мак в никсовую
 		$headers = str_replace( "\r", "\n", $headers_part );

 		/* Берем последний headers */
 		$headers = explode( "\n\n", $headers );
 		$headers_part = end( $headers );

 		/* Парсим headers */
 		$lines = explode( "\n", $headers_part );
 		$headers = array();

 		$headers['start'] = $lines[0];

 		for( $i = 1; $i < count( $lines ); $i++ ){
 			$del_pos = strpos( $lines[$i], ':' );
 			$name = substr( $lines[$i], 0, $del_pos );
 			$value = substr( $lines[$i], $del_pos + 2 );
 			$headers[$name] = $value;
 		}

 		return array(
 			'headers' => $headers,
 			'html' => $body_part
 		);

     }

     /**
      * Добавить 1 произвольный HTPP заголовок к запросу
      *
      * @param string $header
      */
     public function add_header( $header )
     {
         $this->options[CURLOPT_HTTPHEADER][] = $header;
         $this->set( CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER] );
         return $this;
     }

     /**
      * Добавить несколько произвольных http-заголовоков к запросу
      *
      * @param array $headers
      */
     public function add_headers( $headers )
     {
         foreach( $headers as $h )
             $this->options[CURLOPT_HTTPHEADER][] = $h;

         $this->set( CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER] );
         return $this;
     }

     /**
     * Очиситить массив произвольных http-заголовков
     */
     public function clear_headers()
     {
         $this->options[CURLOPT_HTTPHEADER] = array();
         $this->set( CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER] );
         return $this;
     }

     /**
      * Устанавливает настройки куков
      *
      * @param string $cookie
      * Относительный путь до файла для сохранения кук
      * в формате 'wiki' или 'wiki.cookie'
      */
     public function cookie( $cookie )
     {
         if( !strpos( $cookie, '.' ) ) $cookie .= '.cookie';
         curl_setopt( $this->ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/lib/cookie/' . $cookie );
         curl_setopt( $this->ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/lib/cookie/' . $cookie );
         return $this;
     }

     /**
      * Настройка конфигурации для метода POST
      *
      * @param mixed $post
      * array - ассоциативный массив с параметрами
      * false - отлючить обращение методом POST
      */
     public function post( $data )
     {
         if ( $data === false )
         {
             $this->set( CURLOPT_POST, false );
             return $this;
         }

         $this->set( CURLOPT_POST, true );
         $this->set( CURLOPT_POSTFIELDS, http_build_query($data) );
         return $this;
      }

      /**
       * Устанавливает реферер
       *
       * @url string $url
       */
      public function referer( $url ) {
          $this->set( CURLOPT_REFERER, $url );
          return $this;
      }

      /**
       * Устанавливает браузер
       *
       * @agent string $agent
       */
      public function agent( $agent ) {
          $this->set( CURLOPT_USERAGENT, $agent );
          return $this;
      }












     //
}
