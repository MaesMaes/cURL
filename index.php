<?php

    // Засекаем время
    //$start_time = microtime(1);

    include_once 'lib/curl.php';
    require_once 'lib/simple_html_dom.php';

    $c = curl::app( 'https://ru.wikipedia.org/' )
        ->config_load('wiki')
        ;

    // $data =  $c->request( '/' );
    //
    // echo $data['html'];
    //
    // echo '<pre>';
    // print_r( $data['headers'] );
    // echo '</pre>';
    //

    //echo microtime(1) - $start_time;

