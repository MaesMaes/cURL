<?php

    // Засекаем время
    //$start_time = microtime(1);

    include_once 'lib/curl.php';
    include_once 'lib/helpers.php';
    require_once 'lib/simple_html_dom.php';

    set_time_limit( 0 );

    $c = curl::app( 'https://en.wikipedia.org' )
        ->config_load('wiki')
        ;

    $countries = json_decode( file_get_contents( 'res/all.json' ) );


    foreach ($countries as $href => $name )
    {
        $data = $c->request( $href );
        file_put_contents( 'res/country_every/' . $name, $data['html'] );

        // Задержка между запросами от 0 до 2 секунд шоб не забанили
        sleep( mt_rand( 0, 1 ) );
    }



    // echo $done;
    //
    // echo '<pre>';
    // print_r( $contries );
    // echo '</pre>';
    //

    //echo microtime(1) - $start_time;
